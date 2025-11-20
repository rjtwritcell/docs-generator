<?php
namespace App\Services;

use App\Queries\TitleHeadQueries;
use App\Queries\TitleHeadValueQueries;
use DiDom\Document;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;

class ReportService 
{

    public function __construct(
        protected TitleHeadQueries $titleHeadQueries,
        protected TitleHeadValueQueries $titleHeadValueQueries,
        protected FinancialYearService $financialYearService,
    ) {
    }
    public function uploadRAR(string $file, string $financialYear, string $month): void{
        
        $reader = SimpleExcelReader::create($file);

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
            
        $this->upsertTitleHeadValues($titleHeadValues);
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

        $this->upsertTitleHeadValues($titleHeadValues);
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
}