<?php

namespace App\Queries;

use App\Models\ReportTable;
use Illuminate\Support\Collection;

class ReportTableQueries
{

    public function getPUTableNames(): Collection
    {
        return ReportTable::where('is_pu', 1)->get();
    }

    public function getReportTableWithTitleHeadsAndValues(
        string $currentFinancialYear,
        string $previousFinancialYear,
        string $month,
        int $puNO
    ): ?ReportTable {
        return ReportTable::with([
            'titleHeads:id,type,name',
            'titleHeads.titleHeadValues' => function ($query) use ($puNO) {
                $query->where('pu', $puNO)
                    ->select(['id','title_head_id','financial_year','type','pu','month','amount']);
            }
        ])->whereHas('titleHeads.titleHeadValues', function ($query) use ($currentFinancialYear, $previousFinancialYear, $month, $puNO) {

            $query->where(function ($q) use ($currentFinancialYear, $previousFinancialYear, $month, $puNO) {

                // match actual prev year MAR
                $q->where(function ($x) use ($previousFinancialYear, $puNO) {
                    $x->where('type', 'actual')
                        ->where('financial_year', $previousFinancialYear)
                        ->where('month', 'MAR')
                        ->where('pu', $puNO);
                })

                    // match actual prev year selected month (allow null/empty)
                    ->orWhere(function ($x) use ($previousFinancialYear, $month, $puNO) {
                        $x->where('type', 'actual')
                            ->where('financial_year', $previousFinancialYear)
                            ->where('pu', $puNO)
                            ->where(function ($m) use ($month) {
                                $m->where('month', $month)
                                    ->orWhereNull('month')
                                    ->orWhere('month', '');
                            });
                    })

                    // match budget-grant (usually month = "")
                    ->orWhere(function ($x) use ($currentFinancialYear, $puNO) {
                        $x->where('type', 'budget-grant')
                            ->where('financial_year', $currentFinancialYear)
                            ->where('pu', $puNO);
                    })

                    // match actual current year selected month (allow null/empty)
                    ->orWhere(function ($x) use ($currentFinancialYear, $month, $puNO) {
                        $x->where('type', 'actual')
                            ->where('financial_year', $currentFinancialYear)
                            ->where('pu', $puNO)
                            ->where(function ($m) use ($month) {
                                $m->where('month', $month)
                                    ->orWhereNull('month')
                                    ->orWhere('month', '');
                            });
                    });
            });
        })
        ->whereLike('name', "%PU $puNO%")
        ->select(['id', 'name'])
        ->first();
    }
}
