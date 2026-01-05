<?php 

namespace App\Queries;

use App\Models\ReportTable;
use Illuminate\Support\Collection;

class ReportTableQueries {

    public function getPUTableNames (): Collection {
        return ReportTable::where('is_pu', 1)->get();
    }
}