<?php

namespace Database\Seeders;

use App\Enums\ReportTableEnum;
use App\Models\ReportTable;
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
        $reportTable = ReportTable::where('name', ReportTableEnum::DEMAND_WISE->value)->first();
        foreach ($demands as $key => $demand) {
            $titleHead = TitleHead::create([
                ...$demand,
                'type' => 'demand',
                'sort_order' => ($key + 1),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
            $reportTable->titleHeads()->attach($titleHead->id);
        }

        $reportTable = ReportTable::where('name', ReportTableEnum::DEMAND_WISE->value)->first();

        $titleHead = TitleHead::create([
            'no' => null,
            'name' => 'Suspense',
            'type' => 'suspense',
            'sort_order' => null,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        $reportTable->titleHeads()->attach($titleHead->id);

        $reportTable = ReportTable::where('name', ReportTableEnum::PU_WISE->value)->first();
        $puPartA = [
            [
                'no' => '01',
                'name' => 'Salaries and Wages',
                'match_keys' => [ 'actual' => 'PU - 01 - Sal/Wag', 'bg' => '01 - Sal/Wag']
            ],
            [
                'no' => '02',
                'name' => 'Dearness pay and Dearness Allowances',
                'match_keys' => [ 'actual' => 'PU - 02 - DA', 'bg' => '02 - DA']
            ],
            [
                'no' => '03',
                'name' => 'Productivity Linked Bonus',
                'match_keys' => [ 'actual' => 'PU - 03 - PLB', 'bg' => '03 - PLB']
            ],
            [
                'no' => '04',
                'name' => 'House Rent Allowance',
                'match_keys' => [ 'actual' => 'PU - 04 - HRA', 'bg' => '04 - HRA']
            ],
            [
                'no' => '07',
                'name' => 'Transport allowance',
                'match_keys' => [ 'actual' => 'PU - 07 - TPA', 'bg' => '07 - TPA']
            ],
            [
                'no' => '08',
                'name' => 'Matching Contribution of Central Government towards Defined Contribution Pension System.',
                'match_keys' => [ 'actual' => 'PU - 08 - NPS', 'bg' => '08 - NPS']
            ],
            [
                'no' => '10',
                'name' => 'Kilometer allowance',
                'match_keys' => [ 'actual' => 'PU - 10 - KMA', 'bg' => '10 - KMA']
            ],
            [
                'no' => '11',
                'name' => 'Overtime allowance',
                'match_keys' => [ 'actual' => 'PU - 11 - OT', 'bg' => '11 - OT']
            ],
            [
                'no' => '12',
                'name' => 'Night duty allowance',
                'match_keys' => [ 'actual' => 'PU - 12 - NDA', 'bg' => '12 - NDA']
            ],
            [
                'no' => '13',
                'name' => 'Other Allowances',
                'match_keys' => [ 'actual' => 'PU - 13 - OA', 'bg' => '13 - OA']
            ],
            [
                'no' => '14',
                'name' => 'Fees and honoraria',
                'match_keys' => [ 'actual' => 'PU - 14 - FEES / HON.', 'bg' => '14 - FEES / HON.']
            ],
            [
                'no' => '15',
                'name' => 'Transfer allowance',
                'match_keys' => [ 'actual' => 'PU - 15 - TA', 'bg' => '15 - TA']
            ],
            [
                'no' => '16',
                'name' => 'Travelling expenses',
                'match_keys' => [ 'actual' => 'PU - 16 - TE', 'bg' => '16 - TE']
            ],
            [
                'no' => '20',
                'name' => 'Leave encashment during service',
                'match_keys' => [ 'actual' => 'PU - 20 - Leave Salary', 'bg' => '20 - LEAVE SAL']
            ],
            [
                'no' => '25',
                'name' => 'Children education allowance',
                'match_keys' => [ 'actual' => 'PU - 25 - Children Edu. Allow', 'bg' => '25 - CEA']
            ],
            [
                'no' => '29',
                'name' => 'Remuneration to Re-engaged staff, officers and consultants',
                'match_keys' => ['29 - RE-ENGAGE STAFF']
            ],
            [
                'no' => '39',
                'name' => 'Air Travel (Domestic)',
                'match_keys' => [ 'actual' => 'PU - 39 - ATD', 'bg' => '39 - ATD']
            ],
            [
                'no' => '42',
                'name' => 'Arrear Payments-Salary & Wages',
                'match_keys' => [ 'actual' => 'PU - 42 - ARR SALARY', 'bg' => '42 - ARR SALARY']
            ],
            [
                'no' => '43',
                'name' => 'Arrear Payments-Dearness Pay & Dearness Allowances',
                'match_keys' => [ 'actual' => 'PU - 43 - ARR DA', 'bg' => '43 - ARR DA']
            ],
            [
                'no' => '44',
                'name' => 'Arrear Payments - Allowances other than D.A',
                'match_keys' => [ 'actual' => 'PU - 44 - ARR OTH ALW', 'bg' => '44 - ARR OTH ALW']
            ],
            [
                'no' => '53',
                'name' => 'All India Leave Travel Concession (AILTC)',
                'match_keys' => ['53 - AILTC']
            ],
        ];

        // PU Part A
        foreach ($puPartA as $key => $pu) {
            $titleHead = TitleHead::create([
                ...$pu,
                'type' => 'pu',
                'sort_order' => 'A' . ($key + 1),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
            $reportTable->titleHeads()->attach($titleHead->id);
        }

        $puPartB = [
            [
                'no' => '18',
                'name' => 'Office Expenses',
                'match_keys' => [ 'actual' => 'PU - 18 - OE', 'bg' => '18 - CE']
            ],
            [
                'no' => '19',
                'name' => 'Rental for P & T Telephone and call charges including Trunk Calls',
                'match_keys' => [ 'actual' => 'PU - 19 - Phone', 'bg' => '19 - Phone']
            ],
            [
                'no' => '21',
                'name' => 'Advertising Expenses',
                'match_keys' => [ 'actual' => 'PU - 21 - Adv Expenses', 'bg' => '21 - Adv Exp']
            ],
            [
                'no' => '22',
                'name' => 'Utilities (excluding electricity)',
                'match_keys' => [ 'actual' => 'PU - 22 - Util(excl. elec.)', 'bg' => '22 - Util.']
            ],
            [
                'no' => '24',
                'name' => 'Printing and Stationery including Publications.',
                'match_keys' => [ 'actual' => 'PU - 24 - Printing and Stnry', 'bg' => '24 - Stnry']
            ],
            [
                'no' => '26',
                'name' => 'Reimbursement of Medical',
                'match_keys' => [ 'actual' => 'PU - 26 - Medical Expenses','26 - Medical']
            ],
            [
                'no' => '27',
                'name' => 'Cost of materials from stock',
                'match_keys' => [ 'actual' => 'PU - 27 - Materials from stock', 'bg' => '27 - MSTK']
            ],
            [
                'no' => '28',
                'name' => 'Cost of materials - Direct purchase',
                'match_keys' => [ 'actual' => 'PU - 28 - Materials-Dir. purchase', 'bg' => '28 - MDPUR']
            ],
            [
                'no' => '30',
                'name' => 'Cost of electrical energy',
                'match_keys' => [ 'actual' => 'PU - 30 - Cost Of Elec. Energy', 'bg' => '30 - Cost Of Elec.']
            ],
            [
                'no' => '31',
                'name' => "Fuel from 'Stock' for 'Other than Traction Purpose'",
                'match_keys' => [ 'actual' => 'PU - 31 - OF', 'bg' => '31 - OF']
            ],
            [
                'no' => '32',
                'name' => 'Contractual payments',
                'match_keys' => [ 'actual' => 'PU - 32 - CP', 'bg' => '32 - CP']
            ],
            [
                'no' => '33',
                'name' => 'Transfer of debits/credits from other units',
                'match_keys' => [ 'actual' => 'PU - 33 - TRDC', 'bg' => '33 - TRDC']
            ],
            [
                'no' => '36',
                'name' => 'Excise duty paid/payable for purchase of materials',
                'match_keys' => [ 'actual' => 'PU - 36 - Excise Duty', 'bg' => '36 - ED']
            ],
            [
                'no' => '38',
                'name' => 'Sales Tax paid/payable for purchase of materials',
                'match_keys' => [ 'actual' => 'PU - 38 - Sales Tax', 'bg' => '38 - ST']
            ],
            [
                'no' => '49',
                'name' => 'Outsourcing of Manpower for track Maintenance activities',
                'match_keys' => [ 'actual' => 'PU - 49 - O/S M/P TRACK AND SIGNAL MNT', 'bg' => '49 - MANPOWER_TM']
            ],
            [
                'no' => '50',
                'name' => 'Cost of computer hardware/system & Software/application',
                'match_keys' => ['50 - COSTCOMP']
            ],
            [
                'no' => '51',
                'name' => 'Cost of computer consumables',
                'match_keys' => [ 'actual' => 'PU - 51 - COMPCONSUM', 'bg' => '51 - COMPCONSUM']
            ],
            [
                'no' => '52',
                'name' => 'Laptop procured by officers',
                'match_keys' => ['bg' => '52 - LAPALLW']
            ],
            [
                'no' => '72',
                'name' => 'Central GST (CGST)',
                'match_keys' => [ 'actual' => 'PU - 72 - Central GST (CGST)', 'bg' => '72 - CGST']
            ],
            [
                'no' => '73',
                'name' => 'State GST (SGST)',
                'match_keys' => [ 'actual' => 'PU - 73 - State GST (SGST)', 'bg' => '73 - SGST']
            ],
            [
                'no' => '75',
                'name' => 'Integrated GST (IGST)',
                'match_keys' => [ 'actual' => 'PU - 75 - Integrated GST (IGST)', 'bg' => '75 - IGST']
            ],
            [
                'no' => '99',
                'name' => 'Other Expenses',
                'match_keys' => [ 'actual' => 'PU - 99 - OE', 'bg' => '99 - OE']
            ],
        ];

        // PU Part B
        foreach ($puPartB as $key => $pu) {
            $titleHead = TitleHead::create([
                ...$pu,
                'type' => 'pu',
                'sort_order' => 'B' . ($key + 1),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
            $reportTable->titleHeads()->attach($titleHead->id);
        }

        // PU Part C
        $titleHead = TitleHead::create([
            'no' => null,
            'name' => 'Credits',
            'type' => 'pu',
            'sort_order' => 'C1',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        $reportTable->titleHeads()->attach($titleHead->id);


        $this->upsertData(ReportTableEnum::MAJOR_EXPENDITURE, [
            [
                'no' => '27 - I',
                'name' => 'Stock (Excluding PU-27 of Demand No.10)',
            ],
            [
                'no' => '28',
                'name' => 'Cost of materials - Direct purchase',
            ],
            [
                'no' => '32',
                'name' => 'Contractual payments',
            ],
            [
                'no' => '99',
                'name' => 'Other Expenses',
            ],
        ]);

        $this->upsertData(ReportTableEnum::CONTROL_OVER_TA_AND_OT, [
            [
                'no' => '11',
                'name' => 'Overtime allowance',
            ],
            [
                'no' => '16',
                'name' => 'Travelling expenses',
            ],
        ]);

        $this->upsertData(ReportTableEnum::POSITION_OF_CONTROLLABLE_PUs, [
            [
                'no' => '27 - I',
                'name' => 'Stock (Excluding PU-27 of Demand No.10)',
            ],
            [
                'no' => '30 - I',
                'name' => 'NT Elect. (Excluding PU-30 of Demand No.10)',
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
                'no' => '16',
                'name' => 'Travelling expenses',
            ],
            [
                'no' => '28',
                'name' => 'Cost of materials - Direct purchase',
            ],
            [
                'no' => '31',
                'name' => "Fuel from 'Stock' for 'Other than Traction Purpose'",
            ],
            [
                'no' => '32',
                'name' => 'Contractual payments',
            ],
        ]);
    }


    private function upsertData(ReportTableEnum $reportTableEnum, array $data): void
    {
        $reportTable = ReportTable::where('name', $reportTableEnum->value)->first();

        foreach ($data as $key => $item) {
            $titleHead = TitleHead::firstOrCreate(
                [
                    'no' => $item['no'],
                    'name' => $item['name'],
                    'type' => 'pu',
                ],
                [
                    'name' => $item['name'],
                    'sort_order' => $item['sort_order'] ?? ($key + 1),
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]
            );

            $reportTable->titleHeads()->attach($titleHead->id);
        }
    }
}
