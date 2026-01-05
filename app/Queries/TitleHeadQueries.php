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

    public function getPositionOfControllablePUsWithValues(string $currentFinancialYear, string $previousFinancialYear, string $month): Collection
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
                $q->where('name', ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs->value);
            })
            ->orderBy('no')
            ->get();
    }

    // TODO Need to work on the query and verify the existing data with the output of the query
    
    public function getDeptWiseValues(string $currentFinancialYear, string $previousFinancialYear, string $month): Collection
    {
        return TitleHead::query()
            ->where('type', 'dept')
            ->with([
                'reportTables',
                'titleHeadValues' => function ($query) use ($currentFinancialYear, $previousFinancialYear, $month) {

                $query->where(function ($q) use ($currentFinancialYear, $previousFinancialYear, $month) {

                        // match actual prev year MAR
                        $q->where(function ($x) use ($previousFinancialYear) {
                            $x->where('type', 'actual')
                                ->where('financial_year', $previousFinancialYear)
                                ->where('month', 'MAR');
                        })

                        // match actual prev year selected month (allow null/empty)
                        ->orWhere(function ($x) use ($previousFinancialYear, $month) {
                            $x->where('type', 'actual')
                                ->where('financial_year', $previousFinancialYear)
                                ->where(function ($m) use ($month) {
                                    $m->where('month', $month)
                                    ->orWhereNull('month')
                                    ->orWhere('month', '');
                                });
                        })

                        // match budget-grant (usually month = "")
                        ->orWhere(function ($x) use ($currentFinancialYear) {
                            $x->where('type', 'budget-grant')
                                ->where('financial_year', $currentFinancialYear);
                        })

                        // match actual current year selected month (allow null/empty)
                        ->orWhere(function ($x) use ($currentFinancialYear, $month) {
                            $x->where('type', 'actual')
                                ->where('financial_year', $currentFinancialYear)
                                ->where(function ($m) use ($month) {
                                    $m->where('month', $month)
                                    ->orWhereNull('month')
                                    ->orWhere('month', '');
                                });
                        });

                    });
            }])
            ->get();
    }

}
