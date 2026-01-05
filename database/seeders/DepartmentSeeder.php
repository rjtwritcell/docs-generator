<?php

namespace Database\Seeders;

use App\Enums\ReportTableEnum;
use App\Models\ReportTable;
use App\Models\TitleHead;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Accounts' => [
                'match_keys' => ['01 - ACCOUNTS','08180113 - ACCOUNTS'],
                'belongs_to' => [ 
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'General' => [ 
                'match_keys' => ['03 - GEN. ADMN.','08180311 - GEN. ADMN.'],
                'belongs_to' => [ 
                    ReportTableEnum::PU11_OVERTIME_ALLOWANCES->value,
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'Commercial' => [ 
                'match_keys' => ['04 - COMMERCIAL', '08180424 - COMMERCIAL'],
                'belongs_to' => [ 
                    ReportTableEnum::PU11_OVERTIME_ALLOWANCES->value,
                    ReportTableEnum::PU12_NIGHT_DUTY_ALLOWANCES->value,
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'Engineering' => [ 
                'match_keys' => ['05 - ENGINEERING', '08180521 - ENGINEERING'],
                'belongs_to' => [ 
                    ReportTableEnum::PU10_KILOMETER_ALLOWANCES->value,
                    ReportTableEnum::PU11_OVERTIME_ALLOWANCES->value,
                    ReportTableEnum::PU12_NIGHT_DUTY_ALLOWANCES->value,
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'Elec (P) + TRD' => [ 
                'match_keys' => ['06 - ELECTRICAL', '08180619 - ELECTRICAL'],
                'belongs_to' => [ 
                    ReportTableEnum::PU10_KILOMETER_ALLOWANCES->value,
                    ReportTableEnum::PU11_OVERTIME_ALLOWANCES->value,
                    ReportTableEnum::PU12_NIGHT_DUTY_ALLOWANCES->value,
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'Mechanical' => [ 
                'match_keys' => ['07 - MECHANICAL', '08180722 - MECHANICAL'],
                'belongs_to' => [ 
                    ReportTableEnum::PU10_KILOMETER_ALLOWANCES->value,
                    ReportTableEnum::PU11_OVERTIME_ALLOWANCES->value,
                    ReportTableEnum::PU12_NIGHT_DUTY_ALLOWANCES->value,
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'Medical' => [ 
                'match_keys' => ['08 - MEDICAL', '08180825 - MEDICAL'],
                'belongs_to' => [ 
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'Operating' => [ 
                'match_keys' => ['09 - OPERATING', '08180924 - OPERATING'],
                'belongs_to' => [ 
                    ReportTableEnum::PU10_KILOMETER_ALLOWANCES->value,
                    ReportTableEnum::PU11_OVERTIME_ALLOWANCES->value,
                    ReportTableEnum::PU12_NIGHT_DUTY_ALLOWANCES->value,
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'Personnel' => [ 
                'match_keys' => ['10 - PERSONNEL', '08181017 - PERSONNEL'],
                'belongs_to' => [ 
                    ReportTableEnum::PU11_OVERTIME_ALLOWANCES->value,
                    ReportTableEnum::PU12_NIGHT_DUTY_ALLOWANCES->value,
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'S & T' => [ 
                'match_keys' => ['11 - SnT', '08181120 - SnT'],
                'belongs_to' => [ 
                    ReportTableEnum::PU11_OVERTIME_ALLOWANCES->value,
                    ReportTableEnum::PU12_NIGHT_DUTY_ALLOWANCES->value,
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'Store' => [ 
                'match_keys' => ['12 - STORES','08181218 - STORES'],
                'belongs_to' => [ 
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ],
            'Security' => [ 
                'match_keys' => ['13 - SECURITY', '08181326 - SECURITY'],
                'belongs_to' => [ 
                    ReportTableEnum::PU16_TRAVELLING_ALLOWANCES->value,
                    ReportTableEnum::PU26_REIMBURSEMENT_OF_MEDICAL_EXPENSES->value,
                    ReportTableEnum::PU27_STOCK_ITEMS->value,
                    ReportTableEnum::PU28_DIRECT_PURCHASE->value,
                    ReportTableEnum::PU32_CONTRACTUAL_PAYMENTS->value
                ]
            ]
        ];
        $tables = ReportTableEnum::puTables();

        foreach ($tables as $key => $tableEnum) {
            
            foreach ($departments as $departmentKey => $department) {
                $titleHead = TitleHead::firstOrCreate(
                    [
                        'name' => $departmentKey,
                        'type' => 'dept'
                    ],
                    [
                        'name' => $departmentKey,
                        'match_keys' => $department['match_keys'],
                        'sort_order' => null,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ]
                );
                $reportTables = ReportTable::whereIn('name', $department['belongs_to'])->get();
                $reportTables->each(function ($reportTable) use ($titleHead) {
                    $reportTable
                        ->titleHeads()
                        ->syncWithoutDetaching([$titleHead->id]);
                });
            }
        }
    }
}
