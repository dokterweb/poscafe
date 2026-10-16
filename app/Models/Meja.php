<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    protected $table = 'mejas';

    protected $fillable = [
        'nomor',
        'nama',
        'kapasitas',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'kapasitas' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    
    public function orders()
    {
        return $this->hasMany(Order::class, 'meja_id');
    }

}
