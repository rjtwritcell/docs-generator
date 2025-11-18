<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TitleHead extends Model
{
    
    protected $fillable = [
        'no',
        'name',
        'type',
        'notes',
        'sort_order'
    ];
    
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
