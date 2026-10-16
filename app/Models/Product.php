<?php

namespace App\Models;

use App\Models\ConsignmentPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
     protected $fillable = [
        'barcode',
        'name',
        'category_id',
        'hpp',
        'price',
        'stock',
        'track_stock',
        'is_active',
        'product_type',
        'consignment_partner_id',
        'partner_price',
    ];

    protected $casts = [
        'hpp' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
        'partner_price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            OptionGroup::class,
            'product_option_groups',
            'product_id',
            'option_group_id'
        );
    }

    public function consignmentPartner(): BelongsTo
    {
        return $this->belongsTo(ConsignmentPartner::class,'consignment_partner_id');
    }
}
