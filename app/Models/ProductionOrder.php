<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'style',
        'description',
        'total_order_qty',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_order_qty' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function productionSchedules(): HasMany
    {
        return $this->hasMany(ProductionSchedule::class);
    }
}
