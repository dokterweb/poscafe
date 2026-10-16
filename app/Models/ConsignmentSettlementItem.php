<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentSettlementItem extends Model
{
    protected $fillable = [
        'consignment_settlement_id',
        'order_item_id',
        'product_id',
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

    /**
     * Settlement induk.
     */
    public function settlement(): BelongsTo
    {
        return $this->belongsTo(
            ConsignmentSettlement::class,
            'consignment_settlement_id'
        );
    }

    /**
     * Item penjualan POS yang menjadi dasar settlement.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(
            OrderItem::class,
            'order_item_id'
        );
    }

    /**
     * Produk terkait, jika data produknya masih tersedia.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
