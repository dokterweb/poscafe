<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = ['invoice','customer_id','jenis','total_hpp','total_price',
        'discount','final_price','note','date','user_id','payment_status',];

    protected $casts = [
        'date' => 'date',
        'total_hpp' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_price' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
