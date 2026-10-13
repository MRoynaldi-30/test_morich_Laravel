<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'sewing_line_id',
        'production_order_id',
        'start_date',
        'finish_date',
        'total_target',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'finish_date' => 'date',
            'total_target' => 'integer',
        ];
    }

    public function sewingLine(): BelongsTo
    {
        return $this->belongsTo(SewingLine::class);
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function dailyProductions(): HasMany
    {
        return $this->hasMany(DailyProduction::class)
            ->orderBy('production_date');
    }
}
