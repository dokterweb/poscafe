<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentReturnItem extends Model
{
    protected $fillable = [
        'consignment_return_id',
        'product_id',
        'order_item_id',
        'consignment_settlement_id',
        'product_name',
        'quantity',
        'partner_unit_price',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'partner_unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function consignmentReturn(): BelongsTo
    {
        return $this->belongsTo(
            ConsignmentReturn::class,
            'consignment_return_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(
            ConsignmentSettlement::class,
            'consignment_settlement_id'
        );
    }
}
