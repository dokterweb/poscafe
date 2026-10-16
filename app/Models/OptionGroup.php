<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionGroup extends Model
{
    protected $fillable = ['name'];

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_option_groups',
            'option_group_id',
            'product_id'
        );
    }
}