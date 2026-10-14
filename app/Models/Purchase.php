<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
     protected $fillable = [
        'invoice',
        'supplier_id',
        'date',
        'total',
        'note',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'total' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
