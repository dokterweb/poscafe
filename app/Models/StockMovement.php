<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['product_id','type','qty','stock_before','stock_after','reference_type',
        'reference_id','note','user_id',];


    protected $casts = [
        'qty' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'reference_id' => 'integer',
    ];


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}