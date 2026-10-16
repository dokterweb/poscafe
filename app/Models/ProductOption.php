<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOption extends Model
{
    protected $fillable = [
        'option_group_id',
        'name',
        'price_adjustment',
        'is_active',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(
            OptionGroup::class,
            'option_group_id'
        );
    }
}