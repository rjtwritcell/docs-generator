<?php
namespace App\Services;

use App\Queries\TitleHeadQueries;
use App\Queries\TitleHeadValueQueries;


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

    public function __construct(
        protected TitleHeadQueries $titleHeadQueries,
        protected TitleHeadValueQueries $titleHeadValueQueries,
        protected FinancialYearService $financialYearService,
    ) {
    }
    
    public function export(string $financialYear, string $financialMonth)
    {
        $computedFisacalValues = $this->financialYearService->computeFiscalValues($financialYear, $financialMonth);

        $ordinaryWorkExpenses = $this->titleHeadQueries->ordinaryWorkExpense(
            $computedFisacalValues['currentFinancialYear'],
            $computedFisacalValues['previousFinancialYear'],
            $financialMonth
        );

        $mappedOrdinaryWorkExpenses = collect();

        $ordinaryWorkExpenses->each(function ($titleHead) 
        use($computedFisacalValues, $financialMonth, &$mappedOrdinaryWorkExpenses) {
            
            $actualPreviousFinancialYearMar =
                (float) $titleHead->titleHeadValues->where('type', 'actual')
                    ->where('financial_year', $computedFisacalValues['previousFinancialYear'])
                    ->where('month', 'MAR')
                    ->first()->amount;

            $currentYearBudgetGrant =
                (float) $titleHead->titleHeadValues->where('type', 'budget-grant')
                    ->where('financial_year', $computedFisacalValues['currentFinancialYear'])
                    ->first()->amount;

            $actualPreviousYearSelectedMonth = 
                (float) $titleHead->titleHeadValues->where('type', 'actual')
                    ->where('financial_year', $computedFisacalValues['previousFinancialYear'])
                    ->where('month', $financialMonth)
                    ->first()->amount;
            
            $budgetProportionateCurrentYear = round(($currentYearBudgetGrant / 12) * $this->financialYearService->fyMonthIndex($financialMonth), 2);

            $actualCurrentYearSelectedMonth = 
                (float) $titleHead->titleHeadValues->where('type', 'actual')
                    ->where('financial_year', $computedFisacalValues['currentFinancialYear'])
                    ->where('month', $financialMonth)
                    ->first()->amount;
            
            $variation7 = round($actualCurrentYearSelectedMonth - $budgetProportionateCurrentYear, 2);

            $variation7Percent = $budgetProportionateCurrentYear != 0 ? round(($variation7 / $budgetProportionateCurrentYear) * 100, 2) : null;

            $variation9 = round($actualCurrentYearSelectedMonth - $actualPreviousYearSelectedMonth, 2);

            $variation9Percent = $actualPreviousYearSelectedMonth != 0 ? round(($variation9 / $actualPreviousYearSelectedMonth) * 100, 2) : null;

            $mappedOrdinaryWorkExpenses->push([
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

        });

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
        $fileName = "budget_report-" . rand(1, 2000000000). ".xlsx";

        $writer = new Writer();
        $writer->openToBrowser($fileName);

        // Set column widths
        $options = $writer->getOptions();

        // ---------------------------------------
        // Title Rows (MERGED)
        // ---------------------------------------
        $writer->addRow(
            Row::fromValues([
                "Ordinary Working Expenses (Fig in Crores)", 
            ], 
            $titleStyle)
        );
        $options->mergeCells(0,1,10,1); // Merge first row

        $options->setColumnWidth(22, 1);  // Demand No.
        $options->setColumnWidth(8, 2);  // Actual PFY (MAR PFY)
        $options->setColumnWidth(8, 3);  // B.G. CFY
        $options->setColumnWidth(8, 4);  // Actual PFYM
        $options->setColumnWidth(8, 5);  // B.P. CFYM
        $options->setColumnWidth(8, 6);  // Actual CFYM
        $options->setColumnWidth(8, 7);  // Variation (6-5)
        $options->mergeCells(6,2,7,2);
        $options->setColumnWidth(8, 9);  // Variation (6-4)
        $options->mergeCells(8,2,9,2);
        $options->setColumnWidth(40, 11);  // Remarks

        $writer->addRow(
            Row::fromValues([
                "Demand No.", 
                "Actual ". $computedFisacalValues['previousFinancialYear'], 
                "B.G. ". $computedFisacalValues['currentFinancialYear'], 
                "Actual ". $computedFisacalValues['previousFinancialYearMonth'], 
                "B.P. ". $computedFisacalValues['currentFinancialYearMonth'], 
                "Actual Exp ". $computedFisacalValues['currentFinancialYearMonth'], 
                "Variation (6-5)", 
                null, 
                "Variation (6-4)", 
                null,
                "Remarks"
            ], 
            $headerStyle)
        );
        // ---------------------------------------
        // Header Row
        // ---------------------------------------
        $writer->addRow(
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
        
        $mappedOrdinaryWorkExpenses->each(function ($titleHead, $index) 
        use (&$writer, $rowStyle1, $rowStyle2, $firstCellStyle) {

            // first cell as styled Cell
            $first = Cell::fromValue($titleHead['no'] . ' - ' . $titleHead['name'], $firstCellStyle);

            // other cells — create as Cell objects so we can pass them to Row constructor
            $cells = [
                $first,
                Cell::fromValue($titleHead['title_head_values']['actualPreviousFinancialYearMar']),
                Cell::fromValue($titleHead['title_head_values']['currentYearBudgetGrant']),
                Cell::fromValue($titleHead['title_head_values']['actualPreviousYearSelectedMonth']),
                Cell::fromValue($titleHead['title_head_values']['budgetProportionateCurrentYear']),
                Cell::fromValue($titleHead['title_head_values']['actualCurrentYearSelectedMonth']),
                Cell::fromValue($titleHead['title_head_values']['variation7']),
                Cell::fromValue($titleHead['title_head_values']['variation7Percent']),
                Cell::fromValue($titleHead['title_head_values']['variation9']),
                Cell::fromValue($titleHead['title_head_values']['variation9Percent']),
                Cell::fromValue('-'),
            ];

            $rowStyle = $index % 2 == 0 ? $rowStyle1 : $rowStyle2;
            $row = new Row($cells, $rowStyle); // row style still applied (e.g. background), cell styles override where set

            $writer->addRow($row);
            
        });

        $first = Cell::fromValue('Total', $firstCellStyle);
        // other cells — create as Cell objects so we can pass them to Row constructor

        $summedVariation7 = $mappedOrdinaryWorkExpenses->sum('title_head_values.variation7');
        $summedCurrentYearBudgetGrant = $mappedOrdinaryWorkExpenses->sum('title_head_values.currentYearBudgetGrant');
        $calculatedBudgetProportionateCurrentYear = round(($summedCurrentYearBudgetGrant / 12) * $this->financialYearService->fyMonthIndex($financialMonth), 2);
        $calculatedVariation7Percent = round(($summedVariation7 / $calculatedBudgetProportionateCurrentYear) * 100, 2);
        
        $summedActualCurrentYearSelectedMonth = $mappedOrdinaryWorkExpenses->sum('title_head_values.actualCurrentYearSelectedMonth'); 
        $summedActualPreviousYearSelectedMonth = $mappedOrdinaryWorkExpenses->sum('title_head_values.actualPreviousYearSelectedMonth');
        $summedVariation9 = round($summedActualCurrentYearSelectedMonth - $summedActualPreviousYearSelectedMonth, 2);

        $calculatedVariation9Percent = round(($summedVariation9 / $summedActualPreviousYearSelectedMonth) * 100, 2);

        $cells = [
            $first,
            Cell::fromValue($mappedOrdinaryWorkExpenses->sum('title_head_values.actualPreviousFinancialYearMar')),
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

        $row = new Row($cells, $rowStyle2); // row style still applied (e.g. background), cell styles override where set

        $writer->addRow($row);

        $writer->close();
    }

}