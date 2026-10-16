<?php

namespace App\Models;

use App\Models\ConsignmentReturnItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentReturn extends Model
{
    protected $fillable = [
        'return_number',
        'consignment_partner_id',
        'return_date',
        'return_type',
        'reason',
        'financial_treatment',
        'status',
        'notes',
        'created_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'processed_at' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(
            ConsignmentPartner::class,
            'consignment_partner_id'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            ConsignmentReturnItem::class,
            'consignment_return_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
}
