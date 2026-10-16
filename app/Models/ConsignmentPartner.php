<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentPartner extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'address',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

   
    public function products(): HasMany
    {
        return $this->hasMany(Product::class,'consignment_partner_id');
    }

}
