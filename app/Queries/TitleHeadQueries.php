<?php

namespace App\Queries;

use App\Enums\ReportTableEnum;
use App\Models\TitleHead;
use Illuminate\Support\Collection;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;

class TitleHeadQueries
{

    public function getDemandTitleHeads(): Collection
    {
        return TitleHead::query()
            ->where('type', 'demand')
            ->whereHas('reportTables', function ($q) {
                $q->where('name', ReportTableEnum::DEMAND_WISE->value);
            })
            ->orderBy('no')
            ->get();
    }

    public function getPUTitleHeads(): Collection
    {
        return TitleHead::query()
            ->where('type', 'pu')
            ->whereHas('reportTables', function ($q) {
                $q->where('name', ReportTableEnum::PU_WISE->value);
            })
            ->orderBy('no')
            ->get();
    }

    public function getDemandWiseOrdinaryWorkExpense(string $currentFinancialYear, string $previousFinancialYear, string $month): Collection
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
            ->whereHas('reportTables', function ($q) {
                $q->where('name', ReportTableEnum::DEMAND_WISE->value);
            })
            ->where('type', 'demand')
            ->orderBy('no')
            ->get();
    }

    public function getSuspenseTitleHead(): TitleHead
    {
        return TitleHead::query()
            ->where('type', 'suspense')
            ->whereHas('reportTables', function ($q) {
                $q->where('name', ReportTableEnum::DEMAND_WISE->value);
            })
            ->first();
    }

    public function getSuspenseTitleHeadWithValues(string $currentFinancialYear, string $previousFinancialYear, string $month): TitleHead
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
            ->whereHas('reportTables', function ($q) {
                $q->where('name', ReportTableEnum::DEMAND_WISE->value);
            })
            ->where('type', 'suspense')
            ->first();
    }

    public function getPUWiseOrdinaryWorkExpense(string $currentFinancialYear, string $previousFinancialYear, string $month): Collection
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
            ->whereHas('reportTables', function ($q) {
                $q->where('name', ReportTableEnum::PU_WISE->value);
            })
            ->where('type', 'pu')
            ->orderBy('no')
            ->get();
    }

    public function getMajorPU27(): TitleHead
    {
        return TitleHead::query()
            ->where('no', '27 - I')
            ->where('type', 'pu')
            ->orderBy('no')
            ->first();
    }

    public function getMajorPU30(): TitleHead
    {
        return TitleHead::query()
            ->where('no', '30 - I')
            ->where('type', 'pu')
            ->orderBy('no')
            ->first();
    }

    public function getMajorPUWithValues(string $currentFinancialYear, string $previousFinancialYear, string $month): Collection
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
            ->where('type', 'pu')
            ->whereHas('reportTables', function ($q) {
                $q->where('name', ReportTableEnum::MAJOR_EXPENDITURE->value);
            })
            ->orderBy('no')
            ->get();
    }

    public function getTAAndOTWithValues(string $currentFinancialYear, string $previousFinancialYear, string $month): Collection
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
            ->where('type', 'pu')
            ->whereHas('reportTables', function ($q) {
                $q->where('name', ReportTableEnum::CONTROL_OVER_TA_AND_OT->value);
            })
            ->orderBy('no')
            ->get();
    }
}
