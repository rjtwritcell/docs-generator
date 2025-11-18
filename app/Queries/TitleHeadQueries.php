<?php
namespace App\Queries;

use App\Models\TitleHead;
use Illuminate\Support\Collection;

class TitleHeadQueries 
{

    public function getDemandTitleHeads(): Collection
    {
        return TitleHead::query()
            ->where('type', 'demand')
            ->orderBy('no')
            ->get();
    }

    public function ordinaryWorkExpense(string $currentFinancialYear, string $previousFinancialYear, string $month): Collection
    {
        return TitleHead::query()
            ->with(['titleHeadValues' => function ($query) use ($currentFinancialYear, $previousFinancialYear, $month) {
                $query->where(function ($q) use ($previousFinancialYear, $month) {
                    $q->where('type', 'actual')
                    ->where('financial_year', $previousFinancialYear)
                    ->where('month', 'MAR');
                })->orWhere(function ($q) use ($currentFinancialYear, $month) {
                    $q->where('type', 'budget-grant')
                    ->where('financial_year', $currentFinancialYear);
                })->orWhere(function ($q) use ($previousFinancialYear, $month) {
                    $q->where('type', 'actual')
                    ->where('financial_year', $previousFinancialYear)
                    ->where('month', $month);
                })->orWhere(function ($q) use ($currentFinancialYear, $month) {
                    $q->where('type', 'actual')
                    ->where('financial_year', $currentFinancialYear)
                    ->where('month', $month);
                });
            }])
            ->where('type', 'demand')
            ->orderBy('no')
            ->get();
    }

}