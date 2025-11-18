<?php

namespace App\Queries;
use App\Models\TitleHeadValue;

class TitleHeadValueQueries 
{
    public function upsert(array $datRows) {
        TitleHeadValue::upsert(
        $datRows,
        ['title_head_id', 'financial_year', 'month'], // unique keys
        ['amount', 'updated_at']                      // columns to update
        );
    }
}