<?php

namespace Database\Seeders;

use App\Models\TitleHead;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TitleHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demands = [
            [
                'no' => '03',
                'name' => 'General Superintendence and Services',
            ],
            [
                'no' => '04',
                'name' => 'Repairs and Maintenance of Permanent Way and Works',
            ],
            [
                'no' => '05',
                'name' => 'Repairs and Maintenance of Motive Power',
            ],
            [
                'no' => '06',
                'name' => 'Repairs and Maintenance of Carriages and Wagons',
            ],
            [
                'no' => '07',
                'name' => 'Repairs and Maintenance of Plant and Equipment',
            ],
            [
                'no' => '08',
                'name' => 'Operating Expenses - Rolling Stock and Equipment',
            ],
            [
                'no' => '09',
                'name' => 'Operating Expenses - Traffic',
            ],
            [
                'no' => '10',
                'name' => 'Operating Expenses - Fuel',
            ],
            [
                'no' => '11',
                'name' => 'Staff Welfare and Amenities',
            ],
            [
                'no' => '12',
                'name' => 'Miscellaneous Working Expenses',
            ],
            [
                'no' => '13',
                'name' => 'Retirement Benefits and Pensions',
            ]
        ];

        // Demands
        foreach ($demands as $key => $demand) {
            TitleHead::insert([
                ...$demand,
                'type' => 'demand',
                'sort_order' => ($key + 1),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        TitleHead::insert([
            'no' => '13',
            'name' => 'Suspense',
            'type' => 'suspense',
            'sort_order' => null,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $puPartA = [
            [
                'no' => '01',
                'name' => 'Salaries and Wages',
            ],
            [
                'no' => '02',
                'name' => 'Dearness pay and Dearness Allowances',
            ],
            [
                'no' => '03',
                'name' => 'Productivity Linked Bonus',
            ],
            [
                'no' => '04',
                'name' => 'House Rent Allowance',
            ],
            [
                'no' => '07',
                'name' => 'Transport allowance',
            ],
            [
                'no' => '08',
                'name' => 'Matching Contribution of Central Government towards Defined Contribution Pension System.',
            ],
            [
                'no' => '10',
                'name' => 'Kilometer allowance',
            ],
            [
                'no' => '11',
                'name' => 'Overtime allowance',
            ],
            [
                'no' => '12',
                'name' => 'Night duty allowance',
            ],
            [
                'no' => '13',
                'name' => 'Other Allowances',
            ],
            [
                'no' => '14',
                'name' => 'Fees and honoraria',
            ],
            [
                'no' => '15',
                'name' => 'Transfer allowance',
            ],
            [
                'no' => '16',
                'name' => 'Travelling expenses',
            ],
            [
                'no' => '20',
                'name' => 'Leave encashment during service',
            ],
            [
                'no' => '25',
                'name' => 'Children education allowance',
            ],
            [
                'no' => '29',
                'name' => 'Remuneration to Re-engaged staff, officers and consultants',
            ],
            [
                'no' => '39',
                'name' => 'Air Travel (Domestic)',
            ],
            [
                'no' => '42',
                'name' => 'Arrear Payments-Salary & Wages',
            ],
            [
                'no' => '43',
                'name' => 'Arrear Payments-Dearness Pay & Dearness Allowances',
            ],
            [
                'no' => '44',
                'name' => 'Arrear Payments - Allowances other than D.A',
            ],
            [
                'no' => '53',
                'name' => 'All India Leave Travel Concession (AILTC)',
            ],
        ];

        // PU Part A
        foreach ($puPartA as $key => $pu) {
            TitleHead::insert([
                ...$pu,
                'type' => 'pu',
                'sort_order' => 'A' . ($key + 1),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }

        $puPartB = [
            [
                'no' => '18',
                'name' => 'Office Expenses',
            ],
            [
                'no' => '19',
                'name' => 'Rental for P & T Telephone and call charges including Trunk Calls',
            ],
            [
                'no' => '21',
                'name' => 'Advertising Expenses',
            ],
            [
                'no' => '22',
                'name' => 'Utilities (excluding electricity)',
            ],
            [
                'no' => '24',
                'name' => 'Printing and Stationery including Publications.',
            ],
            [
                'no' => '26',
                'name' => 'Reimbursement of Medical',
            ],
            [
                'no' => '27',
                'name' => 'Cost of materials from stock',
            ],
            [
                'no' => '28',
                'name' => 'Cost of materials - Direct purchase',
            ],
            [
                'no' => '30',
                'name' => 'Cost of electrical energy',
            ],
            [
                'no' => '31',
                'name' => "Fuel from 'Stock' for 'Other than Traction Purpose'",
            ],
            [
                'no' => '32',
                'name' => 'Contractual payments',
            ],
            [
                'no' => '33',
                'name' => 'Transfer of debits/credits from other units',
            ],
            [
                'no' => '36',
                'name' => 'Excise duty paid/payable for purchase of materials',
            ],
            [
                'no' => '38',
                'name' => 'Sales Tax paid/payable for purchase of materials',
            ],
            [
                'no' => '49',
                'name' => 'Outsourcing of Manpower for track Maintenance activities',
            ],
            [
                'no' => '50',
                'name' => 'Cost of computer hardware/system & Software/application',
            ],
            [
                'no' => '51',
                'name' => 'Cost of computer consumables',
            ],
            [
                'no' => '52',
                'name' => 'Laptop procured by officers',
            ],
            [
                'no' => '72',
                'name' => 'Central GST (CGST)',
            ],
            [
                'no' => '73',
                'name' => 'State GST (SGST)',
            ],
            [
                'no' => '75',
                'name' => 'Integrated GST (IGST)',
            ],
            [
                'no' => '99',
                'name' => 'Other Expenses',
            ],
            [
                'no' => '98',
                'name' => 'Credits',
            ]
        ];

        // PU Part B
        foreach ($puPartB as $key => $pu) {
            TitleHead::insert([
                ...$pu,
                'type' => 'pu',
                'sort_order' => 'B' . ($key + 1),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
