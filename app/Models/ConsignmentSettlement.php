<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentSettlement extends Model
{
    protected $fillable = [
        'settlement_number',
        'consignment_partner_id',
        'period_start',
        'period_end',
        'total_amount',
        'status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Mitra pemilik settlement.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(
            ConsignmentPartner::class,
            'consignment_partner_id'
        );
    }

    /**
     * Daftar item penjualan dalam settlement.
     */
    public function items(): HasMany
    {
        return $this->hasMany(
            ConsignmentSettlementItem::class,
            'consignment_settlement_id'
        );
    }

    /**
     * User yang membuat settlement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
