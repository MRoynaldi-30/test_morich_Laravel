<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_schedule_id',
        'production_date',
        'base_target_qty',
        'target_qty',
        'actual_qty',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'base_target_qty' => 'integer',
            'target_qty' => 'integer',
            'actual_qty' => 'integer',
        ];
    }

    public function productionSchedule(): BelongsTo
    {
        return $this->belongsTo(ProductionSchedule::class);
    }

    public function getShortageAttribute(): int
    {
        return max(0, $this->target_qty - $this->actual_qty);
    }
}
