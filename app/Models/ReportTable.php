<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportTable extends Model
{
    protected $fillable = [
        'name',
    ];

    public function titleHeads(): BelongsToMany
    {
        return $this->belongsToMany(TitleHead::class, 'report_table_title_head')->withTimestamps();
    }
}
