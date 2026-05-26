<?php

namespace App\Enums;

enum ReportTableEnum: string
{
    case DEMAND_WISE = 'Demand-wise Ordinary Working Expenses';
    case PU_WISE = 'PU-wise Ordinary Working Expenses';
    case MAJOR_EXPENDITURE = 'Major Expenditure PUs';
    case CONTROL_OVER_TA_AND_OT = 'Control over TA and OT';
    case POSITION_OF_CONTROLLABLE_PUs = 'Position of Controllable PUs';
    case PU10_KILOMETER_ALLOWANCES = 'PU 10 Kilometer Allowances';
    case PU11_OVERTIME_ALLOWANCES = 'PU 11 Overtime Allowances';
    case PU12_NIGHT_DUTY_ALLOWANCES = 'PU 12 Night duty Allowances';
    case PU16_TRAVELLING_ALLOWANCES = 'PU 16 Travelling Allowances';
    case PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES = 'PU 26 Reimbursement of Medical Expenses';
    case PU27_STOCK_ITEMS = 'PU 27 Stock Items';
    case PU28_DIRECT_PURCHASE = 'PU 28 Direct Purchase';
    case PU32_CONTRACTUAL_PAYMENTS = 'PU 32 Contractual Payments';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function puTables(): array
    {
        return [
            self::PU10_KILOMETER_ALLOWANCES,
            self::PU11_OVERTIME_ALLOWANCES,
            self::PU12_NIGHT_DUTY_ALLOWANCES,
            self::PU16_TRAVELLING_ALLOWANCES,
            self::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES,
            self::PU27_STOCK_ITEMS,
            self::PU28_DIRECT_PURCHASE,
            self::PU32_CONTRACTUAL_PAYMENTS,
        ];
    }

    public static function allPuNumbers(): array
    {
        $numbers = [];

        foreach (self::cases() as $case) {
            if (preg_match('/^PU(\d+)_/', $case->name, $m)) {
                $numbers[] = $m[1];
            }
        }

        return $numbers;
    }

    public static function puTableMap(): array
    {
        $map = [];

        foreach (self::cases() as $case) {
            if (preg_match('/^PU(\d+)_/', $case->name, $matches)) {
                $map[(int) $matches[1]] = $case;
            }
        }

        return $map;
    }
}
