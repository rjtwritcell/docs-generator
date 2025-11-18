<?php
namespace App\Services;

use App\Queries\TitleHeadQueries;
use App\Queries\TitleHeadValueQueries;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;

class ReportService 
{

    public function __construct(
        protected TitleHeadQueries $titleHeadQueries,
        protected TitleHeadValueQueries $titleHeadValueQueries,
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
        $this->upsertTitleHeadValues($titleHeadValues);
    }
}