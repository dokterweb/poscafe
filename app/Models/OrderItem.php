<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'selected_options',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
        'consignment_partner_id',
        'consignment_partner_name',
        'partner_unit_price',
        'partner_amount',
        'cafe_profit',
    ];

    protected function casts(): array
    {
        return [
            'selected_options' => 'array',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'partner_unit_price' => 'decimal:2',
            'partner_amount' => 'decimal:2',
            'cafe_profit' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }
}