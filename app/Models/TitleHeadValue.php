<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TitleHeadValue extends Model
{
    protected $fillable = [
        'title_head_id',
        'financial_year',
        'type',
        'month',
        'amount'
    ];
}
