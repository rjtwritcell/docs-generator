<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TitleHead extends Model
{
    
    protected $fillable = [
        'no',
        'name',
        'type',
        'notes',
        'sort_order',
        'excel_table',
        'match_keys'
    ];

    protected $casts = [
        'match_keys' => 'array',
    ];

    public function reportTables()
    {
        return $this->belongsToMany(ReportTable::class, 'report_table_title_head')->withTimestamps();
    }
    
    public function titleHeadValues()
    {
        return $this->hasMany(TitleHeadValue::class);
    }

    public function actualValues()
    {
        return $this->hasMany(TitleHeadValue::class)
            ->where('type', 'actual');
    }

    public function budgetGrantValues()
    {
        return $this->hasMany(TitleHeadValue::class)
            ->where('type', 'budget-grant');
    }
}
