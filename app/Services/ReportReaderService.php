<?php
namespace App\Services;

use App\Models\TitleHead;
use App\Queries\TitleHeadQueries;
use App\Queries\TitleHeadValueQueries;
use DiDom\Document;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;

class ReportReaderService 
{

    public function __construct(
        protected TitleHeadQueries $titleHeadQueries,
        protected TitleHeadValueQueries $titleHeadValueQueries,
        protected FinancialYearService $financialYearService,
    ) {
    }
    public function uploadRAR(string $file, string $financialYear, string $month): void{
        
        $reader = SimpleExcelReader::create($file);

        $this->upsertTitleHeadValues(
            $this->readAndPrepareDemandWiseRecordsFromRAR($reader, $financialYear, $month)
        );

        $this->upsertTitleHeadValues(
            $this->readAndPreparePUWiseRecordsFromRAR($reader, $financialYear, $month)
        );

        $this->upsertTitleHeadValues(
            $this->readAndPrepareMajorPUsFromRAR($reader, $financialYear, $month)
        );

        $this->upsertTitleHeadValues(
            $this->readAndPrepareControllablePUsFromRAR($reader, $financialYear, $month)
        );
    }

    private function readAndPrepareMajorPUsFromRAR(SimpleExcelReader $reader, string $financialYear, string $month){
        $titleHead = $this->titleHeadQueries->getMajorPU27();

        $titleHeadValues = [];
        
        $rows = $reader->fromSheetName('GRANT_PU_WISE_SUMMARY(TO_END)  ')
            ->headerOnRow(2)
            ->getRows();
        
        $result = $rows->firstWhere('PU', 'PU - 27');
        $amount = round(($result['\'TOTAL\''] - $result['\'Grant - 10\'']) / 10000, 2);

        $titleHeadValues[] = [
            'title_head_id' => $titleHead['id'],
            'financial_year' => $financialYear,
            'amount' => $amount,
            'month' => $month,
            'type' => 'actual',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return $titleHeadValues;
    }

    private function readAndPrepareDemandWiseRecordsFromRAR(SimpleExcelReader $reader, string $financialYear, string $month): array {
        $titleHeads = $this->titleHeadQueries->getDemandTitleHeads()->keyBy('no')->toArray();

        $titleHeadValues = [];
        
        $reader->fromSheetName('GRANT_WISE  ')
            ->headerOnRow(2)
            ->getRows()
            ->each(function (array $row)
            use ($titleHeads, &$titleHeadValues, $financialYear, $month) {

                $amount = round($row['TO_GROSS'] / 10000000, 2);

                if ($row['DEMAND'] == 13) {
                    $amount = round($row['TO_NET'] / 10000000, 2);
                }

                $titleHeadValues[] = [
                    'title_head_id' => $titleHeads[$row['DEMAND']]['id'],
                    'financial_year' => $financialYear,
                    'month' => $month,
                    'amount' => $amount,
                    'type' => 'actual',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
        
        return $titleHeadValues;
    } 

    private function readAndPreparePUWiseRecordsFromRAR(SimpleExcelReader $reader, string $financialYear, string $month): array{
        $titleHeads = $this->titleHeadQueries->getPUTitleHeads()->keyBy('no')->toArray();

        $titleHeadValues = [];
        
        $rows = $reader->fromSheetName('GRANT_PU_WISE_SUMMARY(TO_END)  ')
            ->headerOnRow(2)
            ->getRows();

        foreach ($titleHeads as $puNo => $titleHead) {

            $result = $rows
                ->firstWhere('PU', 'PU - ' . $puNo);

            if($puNo == 99){
                $pu99 = round(($result['\'TOTAL\''] - $result['\'Grant - 13\'']) / 10000, 2);

                $row = $reader
                    ->fromSheetName('GRANT_DETAIL_PU_WISE  ')
                    ->headerOnRow(2)
                    ->getRows()
                    ->where('DEMAND_CODE', '13')
                    ->where('DETAIL_CODE', '850')
                    ->where('PU_CODE', '99')
                    ->first();

                $amount = $pu99 + round((optional($row)['TO_GROSS'] ?? 0) / 10000000, 2);
               
            } else if($result) {
                $amount = round($result['\'TOTAL\''] / 10000, 2);
            }

            if($puNo && $result) {
                $titleHeadValues[] = [
                    'title_head_id' => $titleHead['id'],
                    'financial_year' => $financialYear,
                    'amount' => $amount,
                    'month' => $month,
                    'type' => 'actual',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

        }

        $credit = $reader->fromSheetName('GRANT_WISE  ')
            ->headerOnRow(2)
            ->getRows()
            ->whereIn('DEMAND', ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'])
            ->sum('TO_CREDIT');
        
        $amount = round($credit / 10000000, 2) * -1;

        $titleHeadValues[] = [
            'title_head_id' => $titleHeads['']['id'],
            'financial_year' => $financialYear,
            'amount' => $amount,
            'month' => $month,
            'type' => 'actual',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return $titleHeadValues;
    }

    private function upsertTitleHeadValues(array $rowsToUpsert): void {
        DB::transaction(function () use ($rowsToUpsert) {
            foreach (array_chunk($rowsToUpsert, 500) as $chunk) {
                $this->titleHeadValueQueries->upsert($chunk);
            }
        });
    }

    public function uploadBG(string $file, string $financialYear): void{
        
        $reader = SimpleExcelReader::create($file);
        
        $this->upsertTitleHeadValues(
            $this->readAndPrepareDemandWiseRecordsFromBG($reader, $financialYear)
        );
        
        $this->upsertTitleHeadValues(
            $this->readAndPreparePUWiseRecordsFromBG($reader, $financialYear)
        );

        $this->upsertTitleHeadValues(
            $this->readAndPrepareMajorPUsFromBG($reader, $financialYear)
        );

        $this->upsertTitleHeadValues(
            $this->readAndPrepareControllablePUsFromBG($reader, $financialYear)
        );

        $this->upsertTitleHeadValues(
            $this->readAndPrepareDeptAndPUWiseRecords($reader, $financialYear)
        );
    
    }

    private function readAndPrepareMajorPUsFromBG(SimpleExcelReader $reader, string $financialYear): array {
    
        $titleHead = $this->titleHeadQueries->getMajorPU27();

        $titleHeadValues = [];

        $rows = $reader->fromSheetName('BSPU_WISE ')
            ->headerOnRow(1)
            ->getRows();

        $amount = $rows
            ->whereIn('SMH', ['01','02','03','04','05','06','07','09', '10', '11'])
            ->sum('27 - MSTK');
        
        $amount = $amount ? round(($amount / 10000), 2) : 0;
        
        $titleHeadValues[] = [
            'title_head_id' => $titleHead['id'],
            'financial_year' => $financialYear,
            'amount' => $amount,
            'type' => 'budget-grant',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return $titleHeadValues;
    }

    private function readAndPrepareDemandWiseRecordsFromBG(SimpleExcelReader $reader, string $financialYear): array {
        $titleHeads = $this->titleHeadQueries->getDemandTitleHeads()->keyBy('no')->toArray();

        $titleHeadValues = [];

        $rows = $reader->fromSheetName('BSPU_WISE ')
            ->headerOnRow(1)
            ->getRows();
        
            
        foreach ($titleHeads as $demandNo => $titleHead) {

            $demandNoInBG = $demandNo - 2;
            $result = $rows
                ->where('SMH', $demandNoInBG);
            
            if($demandNoInBG == 11) {
                $result = $result
                    ->where('HEADCODE', 800)
                    ->whereIn('SUBHEADCODE', [850,890]);
            }

            $amount = round($result->sum('GROSS') / 10000, 2);
            $titleHeadValues[] = [
                'title_head_id' => $titleHead['id'],
                'financial_year' => $financialYear,
                'amount' => $amount,
                'type' => 'budget-grant',
                'created_at' => now(),
                'updated_at' => now(),
            ];

        }

        $result = $rows->where('SMH', '10N')->sum('NET');

        $amount = round($result / 10000, 2);

        $titleHead = $this->titleHeadQueries->getSuspenseTitleHead();

        $titleHeadValues[] = [
            'title_head_id' => $titleHead->id,
            'financial_year' => $financialYear,
            'amount' => $amount,
            'type' => 'budget-grant',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return $titleHeadValues;
    }

    private function readAndPreparePUWiseRecordsFromBG(SimpleExcelReader $reader, string $financialYear): array{
        
        $rows = $reader->fromSheetName('BSPU_WISE ')
            ->headerOnRow(1)
            ->getRows();
        
        $totals = $rows
            ->whereIn('SMH', range(01,11))
            ->reduce(function ($carry, $row) {
                foreach ($row as $key => $value) {

                    // Match keys starting with NN -
                    if (preg_match('/^(\d{2})\s+-/', $key, $m)) {

                        // Extract only the "01", "02", … part
                        $cleanKey = $m[1];

                        $carry[$cleanKey] = ($carry[$cleanKey] ?? 0) + (float)$value;
                    }
                }
                return $carry;
            }, []);
        
        $pu99P1 =  $rows
            ->whereIn('SMH', ['01','02','03','04','05','06','07','08','09','10'])
            ->sum('99 - OE');
        
        $pu98Credit = $rows
            ->whereIn('SMH', ['01','02','03','04','05','06','07','08','09','10'])
            ->sum('98 - Credits');

        $pu99P2 =  $rows
            ->where('SMH', '11')
            ->where('HEADCODE', 800)
            ->where('SUBHEADCODE', 850)
            ->pluck('99 - OE')
            ->first();
            
        $pu99 = $pu99P1 + $pu99P2;

        $titleHeads = $this->titleHeadQueries->getPUTitleHeads()->keyBy('no')->toArray();
        
        $titleHeadValues = [];

        foreach ($titleHeads as $puNo => $titleHead) {

            if($puNo == 99) {
                $amount = $pu99 ? round(($pu99 / 10000), 2) : 0;
            } else if($puNo == "") {
                $amount = $pu98Credit ? -abs(round(($pu98Credit / 10000), 2)) : 0;
            } else if($puNo) {
                $amount = $totals[$puNo] ? round(($totals[$puNo] / 10000), 2) : 0;
            }
            
            $titleHeadValues[] = [
                'title_head_id' => $titleHead['id'],
                'financial_year' => $financialYear,
                'amount' => $amount,
                'type' => 'budget-grant',
                'created_at' => now(),
                'updated_at' => now(),
            ];

        }

        return $titleHeadValues;
    }

    public function uploadRevenueSchedule(string $file, string $year, string $month): void{
        $dom = new Document();
        $dom->loadHtmlFile($file);
        $monthYear = $dom->find('b:contains("For and To End of Month")');

        if (count($monthYear) <= 0) {
            throw new Exception("Could Not parse Month & Year in Revenue Schedule");
        }

        $suspenseValue = $dom->find('td:contains("Net Suspense Heads")');

        if (count($suspenseValue) > 0) {
            $suspenseHeadValue = $suspenseValue[0]->nextSibling()->nextSibling()->nextSibling()->text();

            if (!$suspenseHeadValue) {
                throw new Exception("Unable to find Suspense Head Value.");
            }

            $titleHead = $this->titleHeadQueries->getSuspenseTitleHead();

            $computedFisacalValues = $this->financialYearService->computeFiscalValues($year, $month);

            $this->upsertTitleHeadValues([[
                'title_head_id' => $titleHead->id,
                'financial_year' => $computedFisacalValues['currentFinancialYear'],
                'month' => $month,
                'amount' => round(trim($suspenseHeadValue) / 10000000, 2),
                'type' => 'actual',
                'created_at' => now(),
                'updated_at' => now(),
            ]]);
        }

        // Actual 2022-2023 --> MAR-23 Revenue Schedule
        // Actual DEC-2022 = Actual DEC-2022 Revenue Schedule
        // B.G. 2023-2024 --> B.G > SMH 10N > Gross SUM    
    }

    private function readAndPrepareControllablePUsFromBG(SimpleExcelReader $reader, string $financialYear): array {
    
        $titleHead = $this->titleHeadQueries->getMajorPU30();

        $titleHeadValues = [];

        $rows = $reader->fromSheetName('BSPU_WISE ')
            ->headerOnRow(1)
            ->getRows();

        $amount = $rows
            ->whereIn('SMH', ['01','02','03','04','05','06','07','09', '10', '11'])
            ->sum('30 - Cost Of Elec.');
        
        $amount = $amount ? round(($amount / 10000), 2) : 0;
        
        $titleHeadValues[] = [
            'title_head_id' => $titleHead['id'],
            'financial_year' => $financialYear,
            'amount' => $amount,
            'type' => 'budget-grant',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return $titleHeadValues;
    }

    private function readAndPrepareControllablePUsFromRAR(SimpleExcelReader $reader, string $financialYear, string $month){
        $titleHead = $this->titleHeadQueries->getMajorPU30();

        $titleHeadValues = [];
        
        $rows = $reader->fromSheetName('GRANT_PU_WISE_SUMMARY(TO_END)  ')
            ->headerOnRow(2)
            ->getRows();
        
        $result = $rows->firstWhere('PU', 'PU - 30');
        $amount = round(($result['\'TOTAL\''] - $result['\'Grant - 10\'']) / 10000, 2);

        $titleHeadValues[] = [
            'title_head_id' => $titleHead['id'],
            'financial_year' => $financialYear,
            'amount' => $amount,
            'month' => $month,
            'type' => 'actual',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return $titleHeadValues;
    }

    public function uploadDeptWiseRAR(
        string $file, 
        string $financialYear, 
        string $month
    ): void{
        $reader = SimpleExcelReader::create($file);
        
        $rows = $reader->fromSheetName(' DPW_DW_HW_SW_PW ')
            ->headerOnRow(2)
            ->getRows();

        $formattedYearMonth = $this->financialYearService->formatMonthWithFinancialYearPart(
            $financialYear,
            $month
        );

        $pUsWeNeedToStore = [
            'PU - 10 - KMA',
            'PU - 11 - OT',
            'PU - 12 - NDA',
            'PU - 16 - TE',
            'PU - 26 - Medical Expenses',
            'PU - 27 - Materials from stock',
            'PU - 28 - Materials-Dir. purchase',
            'PU - 32 - CP'
        ];

        $titleHeads = TitleHead::query()
            ->where('type', 'dept')
            ->get();

        $titleHeadValues = $rows
            ->whereIn('PUCODE', $pUsWeNeedToStore)
            ->groupBy('PUCODE')
            ->map(function ($puItems, $puCode) use ($formattedYearMonth, $financialYear, $month, $titleHeads) {
                return $puItems->groupBy('DEPARTMENTCODE')
                    ->map(function ($deptItems, $department) use ($puCode, $formattedYearMonth, $financialYear, $month, $titleHeads) {

                        $amount = $deptItems->sum(function ($row) use ($formattedYearMonth, $financialYear, $month, $titleHeads) {
                            return isset($row[$formattedYearMonth]) ? (float)$row[$formattedYearMonth] : 0;
                        });

                        $titleHead = $titleHeads->first(function ($t) use ($department) {
                            return in_array($department, $t['match_keys']);
                        });

                        $parts = explode(' - ', $puCode);
                        $puNo = $parts[1] ?? null;
                        
                        return [
                            'title_head_id' => $titleHead['id'],
                            'amount'        => $amount,
                            'pu'            => $puNo,
                            'financial_year' => $financialYear,
                            'month'         => $month,
                            'type'          => 'actual'
                        ];

                    })->values();
            })->flatten(1)->values()->toArray();

        $this->upsertTitleHeadValues($titleHeadValues);
    }


    private function readAndPrepareDeptAndPUWiseRecords(SimpleExcelReader $reader, string $financialYear) {
        $titleHeadValues = [];
        $rows = $reader->fromSheetName('BSPU_WISE ')
            ->headerOnRow(1)
            ->getRows();
        
       $pUsWeNeedToStore = collect([
            '10 - KMA',
            '11 - OT',
            '12 - NDA',
            '16 - TE',
            '26 - Medical',
            '27 - MSTK',
            '28 - MDPUR',
            '32 - CP'
        ]);

        $titleHeads = TitleHead::query()
            ->where('type', 'dept')
            ->get();

        $titleHeadValues = [];
        $rows
            ->groupBy('BSPUCODE')
            ->whereNotIn('SMH', ['10N', 'ALL SMHs'])
            ->except(['ALL BSPUs'])
            ->each(function ($deptItems, $deptName) 
                use($pUsWeNeedToStore, $titleHeads, &$titleHeadValues, $financialYear)  {

                $titleHead = $titleHeads->first(function ($t) use ($deptName) {
                    return in_array($deptName, $t['match_keys']);
                });
                
                $pUsWeNeedToStore->each(function($titleHeadMatch) 
                    use($deptItems, $deptName, &$titleHeadValues, $titleHead, $financialYear){

                    $amount = $deptItems->sum($titleHeadMatch);
                    
                    $parts = explode(' - ', $titleHeadMatch);
                    $puNo = $parts[0] ?? null;

                    $titleHeadValues[] = [
                        'title_head_id' => $titleHead['id'],
                        'amount'        => $amount,
                        'pu'            => $puNo,
                        'financial_year' => $financialYear,
                        'month'         => '',
                        'type'          => 'budget-grant'
                    ];

                });
            });

        return $titleHeadValues;
    }
}