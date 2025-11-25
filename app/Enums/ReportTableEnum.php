<?php

namespace App\Enums;

enum ReportTableEnum: string
{
    case DEMAND_WISE = 'Demand-wise Ordinary Working Expenses';
    case PU_WISE = 'PU-wise Ordinary Working Expenses';
    case MAJOR_EXPENDITURE = 'Major Expenditure PUs';
    case CONTROL_OVER_TA_AND_OT = 'Control over TA and OT';
    case POSITION_OF_CONTROLLABLE_PUs = 'Position of Controllable PUs';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
