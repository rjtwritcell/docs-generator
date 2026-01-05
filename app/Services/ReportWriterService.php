<?php

namespace App\Services;

use App\Enums\ReportTableEnum;
use App\Models\TitleHead;
use App\Queries\TitleHeadQueries;
use App\Queries\TitleHeadValueQueries;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\CellAlignment;

class ReportWriterService
{
    protected Writer $writer;
    protected array $styles = [];
    protected array $computedValues = [];

    public function __construct(
        protected TitleHeadQueries $titleHeadQueries,
        protected TitleHeadValueQueries $titleHeadValueQueries,
        protected FinancialYearService $financialYearService,
    ) {
        $this->writer = new Writer();
    }

    public function export(string $financialYear, string $financialMonth): void
    {
        try {
            $this->computedValues = $this->financialYearService->computeFiscalValues($financialYear, $financialMonth);
            $this->initializeStyles();
            
            $fileName = "budget_report-" . rand(1, 2000000000) . ".xlsx";
            $this->writer->openToBrowser($fileName);
            
            $this->prepareDemandWise($financialMonth);
            $this->preparePUWise($financialMonth);
            $this->prepareMajorPUWise($financialMonth);
            $this->prepareTAAndOTTable($financialMonth);
            $this->preparePositionOfControllablePUsTable($financialMonth);

            $this->prepareDepartmentWiseTables($financialMonth);
            
            $this->writer->close();
        } catch (\Throwable $th) {
            Log::error('Error exporting report: ' . $th->getMessage());
        }
    }

    // ============================================================
    // STYLE INITIALIZATION
    // ============================================================
    private function initializeStyles(): void
    {
        // Professional color palette
        $titleBg = Color::rgb(41, 84, 130);          // Deep blue
        $headerBg = Color::rgb(79, 129, 189);        // Medium blue
        $rowAlt1  = Color::rgb(217, 225, 242);       // Light blue
        $rowAlt2  = Color::rgb(242, 242, 242);       // Light gray
        $totalBg  = Color::rgb(12, 118, 158);        // Green
        $groupBg  = Color::rgb(192, 192, 192);       // Silver
        
        $borderAll = $this->createBorder();

        $this->styles = [
            'title' => $this->createTitleStyle($titleBg, $borderAll),
            'groupTitle' => $this->createGroupTitleStyle($groupBg, $borderAll),
            'header' => $this->createHeaderStyle($headerBg, $borderAll),
            'alias' => $this->createAliasStyle($headerBg, $borderAll),
            'row1' => $this->createDataRowStyle($rowAlt1, $borderAll),
            'row2' => $this->createDataRowStyle($rowAlt2, $borderAll),
            'total' => $this->createTotalRowStyle($totalBg, $borderAll),
            'firstCell' => $this->createFirstCellStyle($borderAll),
            'numericCenter' => $this->createNumericCenterStyle($borderAll),
        ];
    }

    private function createBorder(): Border
    {
        return new Border(
            new BorderPart(Border::TOP,    Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT,  Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT,   Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
        );
    }

    private function createTitleStyle(Color|string $bgColor, Border $border): Style
    {
        return (new Style())
            ->setFontSize(16)
            ->setFontName('Calibri')
            ->setFontBold()
            ->setShouldWrapText()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor($bgColor)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setBorder($border);
    }

    private function createGroupTitleStyle(Color|string $bgColor, Border $border): Style
    {
        return (new Style())
            ->setFontSize(12)
            ->setFontName('Calibri')
            ->setFontBold()
            ->setShouldWrapText()
            ->setFontColor(Color::BLACK)
            ->setBackgroundColor($bgColor)
            ->setCellAlignment(CellAlignment::LEFT)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setBorder($border);
    }

    private function createHeaderStyle(Color|string $bgColor, Border $border): Style
    {
        return (new Style())
            ->setFontSize(11)
            ->setFontName('Calibri')
            ->setFontBold()
            ->setShouldWrapText()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor($bgColor)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setBorder($border);
    }

    private function createAliasStyle(Color|string $bgColor, Border $border): Style
    {
        return (new Style())
            ->setFontSize(9)
            ->setFontName('Calibri')
            ->setFontBold()
            ->setShouldWrapText()
            ->setFontColor(Color::BLACK)
            ->setBackgroundColor(Color::rgb(220, 230, 242))
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setBorder($border);
    }

    private function createDataRowStyle(Color|string $bgColor, Border $border): Style
    {
        return (new Style())
            ->setFontSize(10)
            ->setFontName('Calibri')
            ->setShouldWrapText()
            ->setBorder($border)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setFormat('0.00')
            ->setBackgroundColor($bgColor);
    }

    private function createTotalRowStyle(Color|string $bgColor, Border $border): Style
    {
        return (new Style())
            ->setFontSize(10)
            ->setFontName('Calibri')
            ->setFontBold()
            ->setShouldWrapText()
            ->setFontColor(Color::WHITE)
            ->setBorder($border)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setFormat('0.00')
            ->setBackgroundColor($bgColor);
    }

    private function createFirstCellStyle(Border $border): Style
    {
        return (new Style())
            ->setFontSize(10)
            ->setFontName('Calibri')
            ->setFontBold()
            ->setCellAlignment(CellAlignment::LEFT)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setBorder($border);
    }

    private function createNumericCenterStyle(Border $border): Style
    {
        return (new Style())
            ->setFontSize(10)
            ->setFontName('Calibri')
            ->setBorder($border)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setCellVerticalAlignment(CellAlignment::CENTER)
            ->setFormat('0.00');
    }

    // ============================================================
    // DEMAND WISE SECTION
    // ============================================================
    private function prepareDemandWise(string $financialMonth): void
    {
        $demandWiseExpenses = $this->titleHeadQueries->getDemandWiseOrdinaryWorkExpense(
            $this->computedValues['currentFinancialYear'],
            $this->computedValues['previousFinancialYear'],
            $financialMonth
        );

        $suspenseValues = $this->titleHeadQueries->getSuspenseTitleHeadWithValues(
            $this->computedValues['currentFinancialYear'],
            $this->computedValues['previousFinancialYear'],
            $financialMonth
        );

        $this->writeDemandWiseHeader();
        
        $mapped = $this->writeDataRows($demandWiseExpenses, $financialMonth, ReportTableEnum::DEMAND_WISE);
        
        $this->writer->addRow(
            $this->mapSummationRow(
                'Total',
                $this->styles['total'],
                $this->styles['firstCell'],
                $mapped,
                $financialMonth,
                ReportTableEnum::DEMAND_WISE
            )
        );

        $suspenseResult = 
        $this->calculateAndMapRow(
            $suspenseValues, 
            $financialMonth, 
            $this->styles['total'], 
            $this->styles['firstCell'],
            ReportTableEnum::DEMAND_WISE
        );
        
        $mapped->push($suspenseResult['mappedValues']);
        
        $this->writer->addRow($suspenseResult['row']);

        $this->writer->addRow(
            $this->mapSummationRow(
                'Gross Total', 
                $this->styles['total'], 
                $this->styles['firstCell'], 
                $mapped, 
                $financialMonth, 
                ReportTableEnum::DEMAND_WISE
            )
        );
    }

    private function writeDemandWiseHeader(): void
    {
        $options = $this->writer->getOptions();
        
        $this->writer->addRow(
            Row::fromValues(["DEMAND WISE ORDINARY WORKING EXPENSES (Figures in Crores)"], $this->styles['title'])
        );
        $options->mergeCells(0, 1, 10, 1);

        $this->setColumnWidths($options);

        $this->writer->addRow(Row::fromValues($this->getHeaders(ReportTableEnum::DEMAND_WISE), $this->styles['header']));
        $options->mergeCells(6, 2, 7, 2);
        $options->mergeCells(8, 2, 9, 2);

        $this->writer->addRow(Row::fromValues($this->getAliasRow(ReportTableEnum::DEMAND_WISE), $this->styles['alias']));
    }

    private function getHeaders(ReportTableEnum $reportTableEnum): array
    {
        return match ($reportTableEnum) {
            ReportTableEnum::DEMAND_WISE => [
                "Demand No.",
                "Actual " . $this->computedValues['previousFinancialYear'],
                "B.G. " . $this->computedValues['currentFinancialYear'],
                "Actual " . $this->computedValues['previousFinancialYearMonth'],
                "B.P. " . $this->computedValues['currentFinancialYearMonth'],
                "Actual Exp " . $this->computedValues['currentFinancialYearMonth'],
                "Variation (6-5)",
                null,
                "Variation (6-4)",
                null,
                "Remarks"
            ],
            ReportTableEnum::PU_WISE => [
                "PU No.",
                "Actual " . $this->computedValues['previousFinancialYear'],
                "B.G. " . $this->computedValues['currentFinancialYear'],
                "Actual " . $this->computedValues['previousFinancialYearMonth'],
                "B.P. " . $this->computedValues['currentFinancialYearMonth'],
                "Actual " . $this->computedValues['currentFinancialYearMonth'],
                "Variation (6-5)",
                null,
                "Variation (6-4)",
                null,
                "Remarks"
            ],
            ReportTableEnum::MAJOR_EXPENDITURE, ReportTableEnum::CONTROL_OVER_TA_AND_OT => [
                "PU",
                "B.G. " . $this->computedValues['currentFinancialYear'],
                "Actual " . $this->computedValues['previousFinancialYearMonth'],
                "B.P. " . $this->computedValues['currentFinancialYearMonth'],
                "Actual " . $this->computedValues['currentFinancialYearMonth'],
                "Variation (5-3)",
                "Variation % (6/3*100)",
                "Remarks"
            ],
            ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs => [
                "PU",
                "Actual " . $this->computedValues['previousFinancialYear'],
                "B.G. " . $this->computedValues['currentFinancialYear'],
                "Actual " . $this->computedValues['previousFinancialYearMonth'],
                "Actual " . $this->computedValues['currentFinancialYearMonth'],
                "Variation (5-4)",
                "Variation % (6/4*100)",
                "Remarks"
            ],
            ReportTableEnum::PU10_KILOMETER_ALLOWANCES,
            ReportTableEnum::PU11_OVERTIME_ALLOWANCES,
            ReportTableEnum::PU12_NIGHT_DUTY_ALLOWANCES,
            ReportTableEnum::PU16_TRAVELLING_ALLOWANCES,
            ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES,
            ReportTableEnum::PU27_STOCK_ITEMS,
            ReportTableEnum::PU28_DIRECT_PURCHASE,
            ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS, => [
                "Department",
                "B.G. " . $this->computedValues['currentFinancialYear'],
                "Actual " . $this->computedValues['previousFinancialYearMonth'],
                "Actual " . $this->computedValues['currentFinancialYearMonth'],
                "Variation (2-4)",
                "Variation (5/2*100)",
                "Remarks"
            ],
        };
    }

    private function getAliasRow(ReportTableEnum $reportTableEnum): array
    {
        return match ($reportTableEnum) {
            ReportTableEnum::DEMAND_WISE => [1, 2, 3, 4, 5, 6, 7, "% Variation", 8, "% Variation", 9],
            ReportTableEnum::PU_WISE => [1, 2, 3, 4, 5, 6, 7, "% Variation", 8, "% Variation", 9],
            ReportTableEnum::MAJOR_EXPENDITURE, ReportTableEnum::CONTROL_OVER_TA_AND_OT, ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs => [1, 2, 3, 4, 5, 6, 7, 8],
        };

    }

    private function setColumnWidths($options): void
    {
        $options->setColumnWidth(22, 1);
        $options->setColumnWidth(8, 2);
        $options->setColumnWidth(8, 3);
        $options->setColumnWidth(8, 4);
        $options->setColumnWidth(8, 5);
        $options->setColumnWidth(8, 6);
        $options->setColumnWidth(8, 7);
        $options->setColumnWidth(8, 9);
        $options->setColumnWidth(40, 11);
    }

    // ============================================================
    // ROW MAPPING & CALCULATION
    // ============================================================
    private function writeDataRows(Collection $titleHeads, string $financialMonth, ReportTableEnum $reportTableEnum): Collection
    {
        $mapped = collect();
        $titleHeads->each(function ($th, $index) use (&$mapped, $financialMonth, $reportTableEnum) {
            $rowStyle = $index % 2 == 0 ? $this->styles['row1'] : $this->styles['row2'];
            $result = $this->calculateAndMapRow(
                $th, 
                $financialMonth, $rowStyle, $this->styles['firstCell'], 
                $reportTableEnum
            );
            $this->writer->addRow($result['row']);
            $mapped->push($result['mappedValues']);
        });
        return $mapped;
    }

    private function calculateAndMapRow($titleHead, string $financialMonth, Style $rowStyle, Style $firstCellStyle, ReportTableEnum $reportTableEnum): array
    {
        $values = $this->extractTitleHeadValues(
            $titleHead,
            $financialMonth,
            $reportTableEnum
        );
        $mappedValues = $this->buildMappedValues(
            $titleHead, 
            $values, 
            $financialMonth,
            $reportTableEnum
        );
        $title = $mappedValues['no'] ? $mappedValues['no'] . ' - ' . $mappedValues['name'] : $mappedValues['name'];
        
        $rowsArr = [];
        $rowsArr[] = Cell::fromValue($title, $firstCellStyle);
        
        foreach ($mappedValues['title_head_values'] as $key => $value) {
            $rowsArr[] = Cell::fromValue($mappedValues['title_head_values'][$key], $this->styles['numericCenter']);
        }
        
        $rowsArr[] = Cell::fromValue('-', $this->styles['numericCenter']);

        $row = new Row($rowsArr, $rowStyle);
        
        return ['row' => $row, 'mappedValues' => $mappedValues];
    }

    private function extractTitleHeadValues($titleHead, string $financialMonth, ReportTableEnum $reportTableEnum): array
    {
        return match ($reportTableEnum) {
            ReportTableEnum::DEMAND_WISE,
            ReportTableEnum::PU_WISE => [
                'actualPreviousFinancialYearMar' => $this->getAmount($titleHead->titleHeadValues, 'actual', $this->computedValues['previousFinancialYear'], 'MAR'),
                'currentYearBudgetGrant' => $this->getAmount($titleHead->titleHeadValues, 'budget-grant', $this->computedValues['currentFinancialYear']),
                'actualPreviousYearSelectedMonth' => $this->getAmount($titleHead->titleHeadValues, 'actual', $this->computedValues['previousFinancialYear'], $financialMonth),
                'actualCurrentYearSelectedMonth' => $this->getAmount($titleHead->titleHeadValues, 'actual', $this->computedValues['currentFinancialYear'], $financialMonth),
            ],
            ReportTableEnum::MAJOR_EXPENDITURE, ReportTableEnum::CONTROL_OVER_TA_AND_OT => [
                'currentYearBudgetGrant' => $this->getAmount($titleHead->titleHeadValues, 'budget-grant', $this->computedValues['currentFinancialYear']),
                'actualPreviousYearSelectedMonth' => $this->getAmount($titleHead->titleHeadValues, 'actual', $this->computedValues['previousFinancialYear'], $financialMonth),
                'actualCurrentYearSelectedMonth' => $this->getAmount($titleHead->titleHeadValues, 'actual', $this->computedValues['currentFinancialYear'], $financialMonth),
            ],
            ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs => [
                'actualPreviousFinancialYearMar' => $this->getAmount($titleHead->titleHeadValues, 'actual', $this->computedValues['previousFinancialYear']),
                'currentYearBudgetGrant' => $this->getAmount($titleHead->titleHeadValues, 'budget-grant', $this->computedValues['currentFinancialYear']),
                'actualPreviousYearSelectedMonth' => $this->getAmount($titleHead->titleHeadValues, 'actual', $this->computedValues['previousFinancialYear'], $financialMonth),
                'actualCurrentYearSelectedMonth' => $this->getAmount($titleHead->titleHeadValues, 'actual', $this->computedValues['currentFinancialYear'], $financialMonth),
            ],
        };
    }

    private function buildMappedValues($titleHead, array $values, string $financialMonth, ReportTableEnum $reportTableEnum): Collection
    {
        return match ($reportTableEnum) {
            ReportTableEnum::DEMAND_WISE,
            ReportTableEnum::PU_WISE => collect([
                'no' => $titleHead->no,
                'name' => $titleHead->name,
                'title_head_values' => [
                    'actualPreviousFinancialYearMar' => $values['actualPreviousFinancialYearMar'],
                    'currentYearBudgetGrant' => $values['currentYearBudgetGrant'],
                    'actualPreviousYearSelectedMonth' => $values['actualPreviousYearSelectedMonth'],
                    'budgetProportionateCurrentYear' => ($budgetProportionate = round(($values['currentYearBudgetGrant'] / 12) * $this->financialYearService->fyMonthIndex($financialMonth), 2)),
                    'actualCurrentYearSelectedMonth' => $values['actualCurrentYearSelectedMonth'],
                    'variation7' => ($variation7 = round($values['actualCurrentYearSelectedMonth'] - $budgetProportionate, 2)),
                    'variation7Percent' => $budgetProportionate != 0 ? round(($variation7 / $budgetProportionate) * 100, 2) : '-',
                    'variation9' => ($variation9 = round($values['actualCurrentYearSelectedMonth'] - $values['actualPreviousYearSelectedMonth'], 2)),
                    'variation9Percent' => $values['actualPreviousYearSelectedMonth'] != 0 ? round(($variation9 / $values['actualPreviousYearSelectedMonth']) * 100, 2) : '-',
                ]
            ]),
            ReportTableEnum::MAJOR_EXPENDITURE, ReportTableEnum::CONTROL_OVER_TA_AND_OT => collect([
                'no' => $titleHead->no,
                'name' => $titleHead->name,
                'title_head_values' => [
                    'currentYearBudgetGrant' => $values['currentYearBudgetGrant'],
                    'actualPreviousYearSelectedMonth' => $values['actualPreviousYearSelectedMonth'],
                    'budgetProportionateCurrentYear' => ($budgetProportionate = round(($values['currentYearBudgetGrant'] / 12) * $this->financialYearService->fyMonthIndex($financialMonth), 2)),
                    'actualCurrentYearSelectedMonth' => $values['actualCurrentYearSelectedMonth'],
                    'variation6' => ($variation6 = round($values['actualCurrentYearSelectedMonth'] - $values['actualPreviousYearSelectedMonth'], 2)),
                    'variation7Percent' => $values['actualPreviousYearSelectedMonth'] != 0 ? round(($variation6 / $values['actualPreviousYearSelectedMonth']) * 100, 2) : '-',
                ]
            ]),
            ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs => collect([
                'no' => $titleHead->no,
                'name' => $titleHead->name,
                'title_head_values' => [
                    'actualPreviousFinancialYearMar' => $values['actualPreviousFinancialYearMar'],
                    'currentYearBudgetGrant' => $values['currentYearBudgetGrant'],
                    'actualPreviousYearSelectedMonth' => $values['actualPreviousYearSelectedMonth'],
                    'actualCurrentYearSelectedMonth' => $values['actualCurrentYearSelectedMonth'],
                    'variation6' => ($variation6 = round($values['actualCurrentYearSelectedMonth'] - $values['actualPreviousYearSelectedMonth'], 2)),
                    'variation7Percent' => $values['actualPreviousYearSelectedMonth'] != 0 ? round(($variation6 / $values['actualPreviousYearSelectedMonth']) * 100, 2) : '-',
                ]
            ]),
        };


    }

    private function getAmount(Collection $titleHeadValues, string $type, string $financialYear, string $month = null): float
    {
        $query = $titleHeadValues->where('type', $type)->where('financial_year', $financialYear);
        if ($month) $query = $query->where('month', $month);
        $record = $query->first();
        return $record ? (float) $record->amount : 0;
    }

    private function mapSummationRow(string $cellHeaderValue, Style $rowStyle, Style $firstCellStyle, Collection $mappedCollection, string $financialMonth, ReportTableEnum $reportTableEnum): Row
    {
        $sums = $this->calculateSums($mappedCollection, $financialMonth, $reportTableEnum);

        $rowsArr = [];
        $rowsArr[] = Cell::fromValue($cellHeaderValue, $firstCellStyle);
        
        foreach (array_keys($sums) as $key) {
            $rowsArr[] = Cell::fromValue($sums[$key], $this->styles['numericCenter']);
        }

        $rowsArr[] = Cell::fromValue('-', $this->styles['numericCenter']);

        return new Row($rowsArr, $rowStyle);
    }

    private function calculateSums(Collection $mappedCollection, string $financialMonth, ReportTableEnum $reportTableEnum): array
    {
        $sum7 = $mappedCollection->sum('title_head_values.variation7');
        $sumBudgetGrant = $mappedCollection->sum('title_head_values.currentYearBudgetGrant');
        $budgetProportionate = round(($sumBudgetGrant / 12) * $this->financialYearService->fyMonthIndex($financialMonth), 2);

        $sumActualCurrent = $mappedCollection->sum('title_head_values.actualCurrentYearSelectedMonth');
        $sumActualPrevious = $mappedCollection->sum('title_head_values.actualPreviousYearSelectedMonth');
        $sum9 = round($sumActualCurrent - $sumActualPrevious, 2);

        if($reportTableEnum === ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs) {
            return [
                'actualPreviousFinancialYearMar' => $mappedCollection->sum('title_head_values.actualPreviousFinancialYearMar'),
                'currentYearBudgetGrant' => $sumBudgetGrant,
                'actualPreviousYearSelectedMonth' => $sumActualPrevious,
                'actualCurrentYearSelectedMonth' => $sumActualCurrent,
                'variation' => $sum9,
                'variationPercent' => $sumActualPrevious != 0 ? round(($sum9 / $sumActualPrevious) * 100, 2) : 0,
            ];
        } else if($reportTableEnum === ReportTableEnum::MAJOR_EXPENDITURE || $reportTableEnum === ReportTableEnum::CONTROL_OVER_TA_AND_OT) {
            return [
                'currentYearBudgetGrant' => $sumBudgetGrant,
                'actualPreviousYearSelectedMonth' => $sumActualPrevious,
                'budgetProportionateCurrentYear' => $budgetProportionate,
                'actualCurrentYearSelectedMonth' => $sumActualCurrent,
                'variation' => $sum9,
                'variationPercent' => $sumActualPrevious != 0 ? round(($sum9 / $sumActualPrevious) * 100, 2) : 0,
            ];
        } else {
            return [
                'actualPreviousFinancialYearMar' => $mappedCollection->sum('title_head_values.actualPreviousFinancialYearMar'),
                'currentYearBudgetGrant' => $sumBudgetGrant,
                'actualPreviousYearSelectedMonth' => $sumActualPrevious,
                'budgetProportionateCurrentYear' => $budgetProportionate,
                'actualCurrentYearSelectedMonth' => $sumActualCurrent,
                'variation7' => $sum7,
                'variation7Percent' => $budgetProportionate != 0 ? round(($sum7 / $budgetProportionate) * 100, 2) : 0,
                'variation9' => $sum9,
                'variation9Percent' => $sumActualPrevious != 0 ? round(($sum9 / $sumActualPrevious) * 100, 2) : 0,
            ];
        }
    }

    // ============================================================
    // PU WISE SECTION
    // ============================================================
    private function preparePUWise(string $financialMonth): void
    {
        $this->addEmptyRows(4);
        $this->writePUWiseHeader();

        $puHeads = $this->titleHeadQueries->getPUWiseOrdinaryWorkExpense(
            $this->computedValues['currentFinancialYear'],
            $this->computedValues['previousFinancialYear'],
            $financialMonth
        )->sortBy('sort_order', SORT_NATURAL)->values();
        
        $groups = $puHeads->groupBy(
            fn($th) => strtoupper(substr($th->sort_order, 0, 1))
        );
        $groupMappedCollections = [];
        
        $options = $this->writer->getOptions();
        $options->mergeCells(0, 25, 10, 25);
        $options->mergeCells(0, 48, 10, 48);
        
        foreach ($groups->keys()->sort()->values()->all() as $groupKey) {
            $titleHeads = $groups->get($groupKey);
            
            if ($groupKey !== 'C') {
                $this->writer->addRow(Row::fromValues(["Group " . $groupKey], $this->styles['title']));
                $mapped = $this->writeDataRows($titleHeads, $financialMonth, ReportTableEnum::PU_WISE);
                $this->writer->addRow($this->mapSummationRow("Total ({$groupKey})", $this->styles['row2'], $this->styles['firstCell'], $mapped, $financialMonth, ReportTableEnum::PU_WISE));
            } else {
                $mapped = $this->extractMappedValuesOnly($titleHeads, $financialMonth, ReportTableEnum::PU_WISE);
            }
            
            $groupMappedCollections[$groupKey] = $mapped;
        }

        $this->writeFinalTotals($groupMappedCollections, $financialMonth, ReportTableEnum::PU_WISE);
    }

    private function writePUWiseHeader(): void
    {
        $options = $this->writer->getOptions();
        $this->writer->addRow(Row::fromValues(["PU WISE ORDINARY WORKING EXPENSES (Figures in Crores)"], $this->styles['title']));
        $options->mergeCells(0, 22, 10, 22);

        $this->writer->addRow(Row::fromValues($this->getHeaders(ReportTableEnum::PU_WISE), $this->styles['header']));
        $options->mergeCells(6, 23, 7, 23);
        $options->mergeCells(8, 23, 9, 23);

        $this->writer->addRow(Row::fromValues($this->getAliasRow(ReportTableEnum::PU_WISE), $this->styles['alias']));
    }

    private function extractMappedValuesOnly(Collection $titleHeads, string $financialMonth, ReportTableEnum $reportTableEnum): Collection
    {
        $mapped = collect();
        $titleHeads->each(function ($th) use (&$mapped, $financialMonth, $reportTableEnum) {
            $result = $this->calculateAndMapRow($th, $financialMonth, $this->styles['row1'], 
                $this->styles['firstCell'],
                $reportTableEnum
            );
            $mapped->push($result['mappedValues']);
        });
        return $mapped;
    }

    private function writeFinalTotals(array $groupMappedCollections, string $financialMonth, ReportTableEnum $reportTableEnum): void
    {
        $combinedAB = collect();
        if (isset($groupMappedCollections['A'])) $combinedAB = $combinedAB->concat($groupMappedCollections['A']);
        if (isset($groupMappedCollections['B'])) $combinedAB = $combinedAB->concat($groupMappedCollections['B']);

        if ($combinedAB->isNotEmpty()) {
            $this->writer->addRow(
                $this->mapSummationRow("GROSS TOTAL (A + B)", $this->styles['total'], $this->styles['firstCell'], $combinedAB, $financialMonth, $reportTableEnum));
        }

        $this->writer->addRow($this->mapSummationRow("CREDITS (C)", $this->styles['total'], $this->styles['firstCell'], $groupMappedCollections['C'], $financialMonth, $reportTableEnum));

        $allCombined = $combinedAB->concat($groupMappedCollections['C'] ?? collect());
        if ($allCombined->isNotEmpty()) {
            $this->writer->addRow($this->mapSummationRow("NET TOTAL (A + B - CREDITS)", $this->styles['total'], $this->styles['firstCell'], $allCombined, $financialMonth, $reportTableEnum));
        }
    }

    private function addEmptyRows(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->writer->addRow(Row::fromValues([]));
        }
    }

    public function prepareMajorPUWise(string $financialMonth): void
    {
        $this->addEmptyRows(4);
        $this->writeMajorExpenditurePUHeader();

        $majorPUWithValues = $this->titleHeadQueries->getMajorPUWithValues(
            $this->computedValues['currentFinancialYear'],
            $this->computedValues['previousFinancialYear'],
            $financialMonth
        );

        $mapped = $this->writeDataRows($majorPUWithValues, $financialMonth, ReportTableEnum::MAJOR_EXPENDITURE);

        $this->writer->addRow(
            $this->mapSummationRow(
                'Total', 
                $this->styles['total'], 
                $this->styles['firstCell'], 
                $mapped, 
                $financialMonth, 
                ReportTableEnum::MAJOR_EXPENDITURE
            )
        );
    }

    private function writeMajorExpenditurePUHeader(): void
    {
        $options = $this->writer->getOptions();
        $this->writer->addRow(Row::fromValues(["MAJOR EXPENDITURE PUs (Figures in Crores)"], $this->styles['title']));
        $options->mergeCells(0, 79, 7, 79);

        $this->writer->addRow(Row::fromValues($this->getHeaders(ReportTableEnum::MAJOR_EXPENDITURE), $this->styles['header']));
        $this->writer->addRow(Row::fromValues($this->getAliasRow(ReportTableEnum::MAJOR_EXPENDITURE), $this->styles['alias']));
    }

    
    public function prepareTAAndOTTable(string $financialMonth): void
    {
        $this->addEmptyRows(4);
        $this->writeTAandOTTableHeader();
        
        $tAAndOTWithValues = $this->titleHeadQueries->getTAAndOTWithValues(
            $this->computedValues['currentFinancialYear'],
            $this->computedValues['previousFinancialYear'],
            $financialMonth
        );

        $mapped = $this->writeDataRows(
            $tAAndOTWithValues,
            $financialMonth,
            ReportTableEnum::CONTROL_OVER_TA_AND_OT
        );

        $this->writer->addRow(
            $this->mapSummationRow(
                'Total', 
                $this->styles['total'], 
                $this->styles['firstCell'], 
                $mapped, 
                $financialMonth, 
                ReportTableEnum::CONTROL_OVER_TA_AND_OT
            )
        );
    }

    private function writeTAandOTTableHeader(): void
    {
        $options = $this->writer->getOptions();
        $this->writer->addRow(Row::fromValues(["Control over TA & OT (Figures in Crores)"],
        $this->styles['title']));
        $options->mergeCells(0,
        91, 7, 91);

        $this->writer->addRow(Row::fromValues($this->getHeaders(ReportTableEnum::CONTROL_OVER_TA_AND_OT), $this->styles['header']));
        $this->writer->addRow(Row::fromValues($this->getAliasRow(ReportTableEnum::CONTROL_OVER_TA_AND_OT), $this->styles['alias']));
    }
    
    public function preparePositionOfControllablePUsTable(string $financialMonth): void
    {
        $this->addEmptyRows(count: 4);
        $this->writePositionOfControllablePUsTableHeader();
        
        $titleHeadValues = $this->titleHeadQueries->getPositionOfControllablePUsWithValues(
            $this->computedValues['currentFinancialYear'],
            $this->computedValues['previousFinancialYear'],
            $financialMonth
        );

        $mapped = $this->writeDataRows($titleHeadValues, $financialMonth, ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs);
        
        $this->writer->addRow(
            $this->mapSummationRow(
                'Total', 
                $this->styles['total'], 
                $this->styles['firstCell'], 
                $mapped, 
                $financialMonth, 
                ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs
            )
        );

    }

    private function writePositionOfControllablePUsTableHeader(): void
    {
        $options = $this->writer->getOptions();
        $this->writer->addRow(Row::fromValues(["Position of Controllable PUs (Figures in Crores)"], $this->styles['title']));
        
        $options->mergeCells(0, 101, 7, 101);

        $this->writer->addRow(Row::fromValues($this->getHeaders(ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs), $this->styles['header']));
        $this->writer->addRow(Row::fromValues($this->getAliasRow(ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs), $this->styles['alias']));
    }

    private function prepareDepartmentWiseTables(string $financialMonth): void
    {
        DB::enableQueryLog();

        // $data = $this->titleHeadQueries->getDeptWiseValues(
        //     $this->computedValues['currentFinancialYear'],
        //     $this->computedValues['previousFinancialYear'],
        //     $financialMonth
        // )->flatMap(function ($dept) {
        //     return collect($dept->titleHeadValues)->map(function ($value) use ($dept) {
        //         return [
        //             'id' => $value->id,
        //             'title_head_id' => $value->title_head_id,
        //             'title_head_name' => $dept->name,
        //             'financial_year' => $value->financial_year,
        //             'type' => $value->type,
        //             'pu' => $value->pu,
        //             'month' => $value->month,
        //             'amount' => $value->amount,
        //             'created_at' => $value->created_at,
        //             'updated_at' => $value->updated_at,
        //         ];
        //     });
        // })->groupBy('pu');

        $data = $this->titleHeadQueries->getDeptWiseValues(
            $this->computedValues['currentFinancialYear'],
            $this->computedValues['previousFinancialYear'],
            $financialMonth
        );
        
        // $data->each(function ($items, $pu) {
        //     $this->addEmptyRows(count: 4);
            
        //     Log::info("PU: " . $pu);
        //     $items->each(function ($item) {
        //         Log::info($item);
        //     });
        // });
        Log::info($data);
    }

    private function writeDeptWiseHeader(string $puNo, int $lineNo): void
    {
        $options = $this->writer->getOptions();
        $this->writer->addRow(Row::fromValues(["PU No. " . $puNo . " (Figures in Thousands)"],
        $this->styles['title']));

        $options->mergeCells(0,$lineNo, 7, 91);

        $this->writer->addRow(Row::fromValues($this->getHeaders(ReportTableEnum::CONTROL_OVER_TA_AND_OT), $this->styles['header']));
        $this->writer->addRow(Row::fromValues($this->getAliasRow(ReportTableEnum::CONTROL_OVER_TA_AND_OT), $this->styles['alias']));
    }
}
