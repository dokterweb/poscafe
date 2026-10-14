<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    protected $fillable = ['sale_id','product_id','price','qty','discount','total','total_hpp'];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'total_hpp' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
