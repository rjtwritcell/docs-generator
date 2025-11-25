<?php

namespace Database\Seeders;

use App\Enums\ReportTableEnum;
use App\Models\ReportTable;
use Illuminate\Database\Seeder;

class ReportTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = ReportTableEnum::values();

        ReportTable::insert(
            array_map(fn($name) => [
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ], $tables)
        );
    }
}
