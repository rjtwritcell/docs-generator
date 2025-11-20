<?php

namespace App\Services;

use App\Models\TitleHead;
use App\Queries\TitleHeadQueries;
use App\Queries\TitleHeadValueQueries;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Row;
use Illuminate\Support\Facades\Response;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Writer\Common\Creator\Style\StyleBuilder;

class ReportWriterService
{
    protected Writer $writer;

    public function __construct(
        protected TitleHeadQueries $titleHeadQueries,
        protected TitleHeadValueQueries $titleHeadValueQueries,
        protected FinancialYearService $financialYearService,
    ) {
        $this->writer = new Writer();
    }

    public function export(string $financialYear, string $financialMonth)
    {
        try {

            $computedFisacalValues = $this->financialYearService->computeFiscalValues($financialYear, $financialMonth);

            $this->prepareDemandWiseOrdinaryWorkingExpensesTable(
                $financialMonth,
                $computedFisacalValues
            );


            $this->writer->close();
        } catch (\Throwable $th) {
            Log::error('Error exporting report: ' . $th->getMessage());
        }
    }

    public function prepareDemandWiseOrdinaryWorkingExpensesTable(string $financialMonth, array $computedFisacalValues)
    {
        $demandWiseOrdinaryWorkExpenses = $this->titleHeadQueries->ordinaryWorkExpense(
            $computedFisacalValues['currentFinancialYear'],
            $computedFisacalValues['previousFinancialYear'],
            $financialMonth
        );

        $suspenseTitleHeadValues = $this->titleHeadQueries->getSuspenseTitleHeadWithValues(
            $computedFisacalValues['currentFinancialYear'],
            $computedFisacalValues['previousFinancialYear'],
            $financialMonth
        );

        $mappedOrdinaryWorkExpenses = collect();

        // ---------------------------------------
        // Colors
        // ---------------------------------------
        $headerBg = Color::rgb(216, 178, 92);       // beige
        $rowAlt1  = Color::rgb(247, 229, 222);       // lighter beige
        $rowAlt2  = Color::rgb(239, 224, 189);       // slightly darker beige

        // ---------------------------------------
        // Borders
        // ---------------------------------------
        $borderAll = new Border(
            new BorderPart(Border::TOP,    Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT,  Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT,   Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
        );

        // ---------------------------------------
        // STYLES
        // ---------------------------------------
        $titleStyle = (new Style())
            ->setFontBold()
            ->setFontSize(16)
            ->setFontName('Montserrat')
            ->setShouldWrapText()
            ->setBackgroundColor($headerBg)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setBorder($borderAll);

        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontSize(12)
            ->setFontName('Montserrat')
            ->setShouldWrapText()
            ->setBackgroundColor($headerBg)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setBorder($borderAll);

        $aliasRow = (new Style())
            ->setFontBold()
            ->setFontSize(10)
            ->setFontName('Montserrat')
            ->setShouldWrapText()
            ->setBackgroundColor($headerBg)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setBorder($borderAll);

        $rowStyle1 = (new Style())
            ->setFontName('Montserrat')
            ->setShouldWrapText()
            ->setBorder($borderAll)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setFormat('00.00')
            ->setBackgroundColor($rowAlt1);

        $rowStyle2 = (new Style())
            ->setFontName('Montserrat')
            ->setShouldWrapText()
            ->setBorder($borderAll)
            ->setFormat('00.00')
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setBackgroundColor($rowAlt2);

        // ---------------------------------------
        // START EXCEL OUTPUT
        // ---------------------------------------
        $fileName = "budget_report-" . rand(1, 2000000000) . ".xlsx";

        $this->writer->openToBrowser($fileName);

        // Set column widths
        $options = $this->writer->getOptions();

        // ---------------------------------------
        // Title Rows (MERGED)
        // ---------------------------------------
        $this->writer->addRow(
            Row::fromValues(
                [
                    "Demand wise Ordinary Working Expenses (Fig in Crores)",
                ],
                $titleStyle
            )
        );
        $options->mergeCells(0, 1, 10, 1); // Merge first row

        $options->setColumnWidth(22, 1);  // Demand No.
        $options->setColumnWidth(8, 2);  // Actual PFY (MAR PFY)
        $options->setColumnWidth(8, 3);  // B.G. CFY
        $options->setColumnWidth(8, 4);  // Actual PFYM
        $options->setColumnWidth(8, 5);  // B.P. CFYM
        $options->setColumnWidth(8, 6);  // Actual CFYM
        $options->setColumnWidth(8, 7);  // Variation (6-5)
        $options->mergeCells(6, 2, 7, 2);
        $options->setColumnWidth(8, 9);  // Variation (6-4)
        $options->mergeCells(8, 2, 9, 2);
        $options->setColumnWidth(40, 11);  // Remarks

        $this->writer->addRow(
            Row::fromValues(
                [
                    "Demand No.",
                    "Actual " . $computedFisacalValues['previousFinancialYear'],
                    "B.G. " . $computedFisacalValues['currentFinancialYear'],
                    "Actual " . $computedFisacalValues['previousFinancialYearMonth'],
                    "B.P. " . $computedFisacalValues['currentFinancialYearMonth'],
                    "Actual Exp " . $computedFisacalValues['currentFinancialYearMonth'],
                    "Variation (6-5)",
                    null,
                    "Variation (6-4)",
                    null,
                    "Remarks"
                ],
                $headerStyle
            )
        );
        // ---------------------------------------
        // Header Row
        // ---------------------------------------
        $this->writer->addRow(
            Row::fromValues([
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                "% Variation",
                8,
                "% Variation",
                9,
            ], $aliasRow)
        );

        $firstCellStyle = (new Style())
            ->setFontBold();

        $mappedOrdinaryWorkExpenses = $this->calculateAndMapRows(
            $demandWiseOrdinaryWorkExpenses,
            $computedFisacalValues,
            $financialMonth,
            $rowStyle1,
            $rowStyle2,
            $firstCellStyle,
        );

        $row = $this->mapSummationRow(
            'Total',
            $rowStyle2,
            $firstCellStyle,
            $mappedOrdinaryWorkExpenses,
            $financialMonth
        );

        $this->writer->addRow($row);

        $result = $this->calculateAndMapRow(
            $suspenseTitleHeadValues,
            $computedFisacalValues,
            $financialMonth,
            $rowStyle1,
            $firstCellStyle
        );

        $mappedOrdinaryWorkExpenses->push($result['mappedValues']);

        $this->writer->addRow($result['row']);

        Log::info('Mapped Suspense Title Head Values: ', $mappedOrdinaryWorkExpenses->toArray());

        $row = $this->mapSummationRow(
            'Gross Total',
            $rowStyle2,
            $firstCellStyle,
            $mappedOrdinaryWorkExpenses,
            $financialMonth
        );

        $this->writer->addRow($row);
    }

    private function getAmount(Collection $titleHeadValues, string $type, string  $financialYear, string $month = null)
    {
        $query = $titleHeadValues->where('type', $type)
            ->where('financial_year', $financialYear);

        if ($month) {
            $query = $query->where('month', $month);
        }

        $record = $query->first();

        return $record ? (float) $record->amount : 0;
    }

    private function calculateAndMapRow(TitleHead $titleHead, array $computedFisacalValues, string $financialMonth, Style $rowStyle, Style $firstCellStyle): array
    {

        $actualPreviousFinancialYearMar =
            $this->getAmount(
                $titleHead->titleHeadValues,
                'actual',
                $computedFisacalValues['previousFinancialYear'],
                'MAR'
            );
        $currentYearBudgetGrant =
            $this->getAmount(
                $titleHead->titleHeadValues,
                'budget-grant',
                $computedFisacalValues['currentFinancialYear']
            );

        $actualPreviousYearSelectedMonth =
            $this->getAmount(
                $titleHead->titleHeadValues,
                'actual',
                $computedFisacalValues['previousFinancialYear'],
                $financialMonth
            );

        $budgetProportionateCurrentYear = round(($currentYearBudgetGrant / 12) * $this->financialYearService->fyMonthIndex($financialMonth), 2);

        $actualCurrentYearSelectedMonth =
            $this->getAmount(
                $titleHead->titleHeadValues,
                'actual',
                $computedFisacalValues['currentFinancialYear'],
                $financialMonth
            );

        $variation7 = round($actualCurrentYearSelectedMonth - $budgetProportionateCurrentYear, 2);

        $variation7Percent = $budgetProportionateCurrentYear != 0 ? round(($variation7 / $budgetProportionateCurrentYear) * 100, 2) : null;

        $variation9 = round($actualCurrentYearSelectedMonth - $actualPreviousYearSelectedMonth, 2);

        $variation9Percent = $actualPreviousYearSelectedMonth != 0 ? round(($variation9 / $actualPreviousYearSelectedMonth) * 100, 2) : null;

        $mappedValues = collect([
            'no' => $titleHead->no,
            'name' => $titleHead->name,
            'title_head_values' => [
                'actualPreviousFinancialYearMar' => $actualPreviousFinancialYearMar,
                'currentYearBudgetGrant' => $currentYearBudgetGrant,
                'actualPreviousYearSelectedMonth' => $actualPreviousYearSelectedMonth,
                'budgetProportionateCurrentYear' => $budgetProportionateCurrentYear,
                'actualCurrentYearSelectedMonth' => $actualCurrentYearSelectedMonth,
                'variation7' => $variation7,
                'variation7Percent' => $variation7Percent,
                'variation9' => $variation9,
                'variation9Percent' => $variation9Percent,
            ]
        ]);

        $title = $mappedValues['no'] ? $mappedValues['no'] . ' - ' . $mappedValues['name'] : $mappedValues['name'];
        $resultantRow = new Row([
            Cell::fromValue($title, $firstCellStyle),
            Cell::fromValue($mappedValues['title_head_values']['actualPreviousFinancialYearMar']),
            Cell::fromValue($mappedValues['title_head_values']['currentYearBudgetGrant']),
            Cell::fromValue($mappedValues['title_head_values']['actualPreviousYearSelectedMonth']),
            Cell::fromValue($mappedValues['title_head_values']['budgetProportionateCurrentYear']),
            Cell::fromValue($mappedValues['title_head_values']['actualCurrentYearSelectedMonth']),
            Cell::fromValue($mappedValues['title_head_values']['variation7']),
            Cell::fromValue($mappedValues['title_head_values']['variation7Percent']),
            Cell::fromValue($mappedValues['title_head_values']['variation9']),
            Cell::fromValue($mappedValues['title_head_values']['variation9Percent']),
            Cell::fromValue('-'),
        ], $rowStyle);

        return ['row' => $resultantRow, 'mappedValues' => $mappedValues];
    }

    private function calculateAndMapRows(Collection $titleHeads, array $computedFisacalValues, string $financialMonth, Style $rowStyle1, Style $rowStyle2, Style $firstCellStyle): Collection
    {
        $mappedOrdinaryWorkExpenses = collect();
        $titleHeads->each(function ($titleHead, $index)
        use ($computedFisacalValues, $financialMonth, $mappedOrdinaryWorkExpenses, $rowStyle1, $rowStyle2, $firstCellStyle) {
            $rowStyle = $index % 2 == 0 ? $rowStyle1 : $rowStyle2;
            $result = $this->calculateAndMapRow(
                $titleHead,
                $computedFisacalValues,
                $financialMonth,
                $rowStyle,
                $firstCellStyle
            );

            $this->writer->addRow($result['row']);
            $mappedOrdinaryWorkExpenses->push($result['mappedValues']);
        });

        return $mappedOrdinaryWorkExpenses;
    }

    private function mapSummationRow(string $cellHeaderValue, Style $rowStyle, Style $firstCellStyle, Collection $mappedCollection, string $financialMonth): Row
    {

        $first = Cell::fromValue($cellHeaderValue, $firstCellStyle);

        $summedVariation7 = $mappedCollection->sum('title_head_values.variation7');
        $summedCurrentYearBudgetGrant = $mappedCollection->sum('title_head_values.currentYearBudgetGrant');
        $calculatedBudgetProportionateCurrentYear = round(($summedCurrentYearBudgetGrant / 12) * $this->financialYearService->fyMonthIndex($financialMonth), 2);
        $calculatedVariation7Percent = $calculatedBudgetProportionateCurrentYear != 0 ? round(($summedVariation7 / $calculatedBudgetProportionateCurrentYear) * 100, 2) : 0;

        $summedActualCurrentYearSelectedMonth = $mappedCollection->sum('title_head_values.actualCurrentYearSelectedMonth');
        $summedActualPreviousYearSelectedMonth = $mappedCollection->sum('title_head_values.actualPreviousYearSelectedMonth');
        $summedVariation9 = round($summedActualCurrentYearSelectedMonth - $summedActualPreviousYearSelectedMonth, 2);

        $calculatedVariation9Percent = $summedActualPreviousYearSelectedMonth != 0 ? round(($summedVariation9 / $summedActualPreviousYearSelectedMonth) * 100, 2) : 0;

        $cells = [
            $first,
            Cell::fromValue($mappedCollection->sum('title_head_values.actualPreviousFinancialYearMar')),
            Cell::fromValue($summedCurrentYearBudgetGrant),
            Cell::fromValue($summedActualPreviousYearSelectedMonth),
            Cell::fromValue($calculatedBudgetProportionateCurrentYear),
            Cell::fromValue($summedActualCurrentYearSelectedMonth),
            Cell::fromValue($summedVariation7),
            Cell::fromValue($calculatedVariation7Percent),
            Cell::fromValue($summedVariation9),
            Cell::fromValue($calculatedVariation9Percent),
            Cell::fromValue(value: '-'),
        ];

        return new Row($cells, $rowStyle);
    }
}
