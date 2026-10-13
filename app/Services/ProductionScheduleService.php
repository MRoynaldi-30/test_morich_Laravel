<?php

namespace App\Services;

use App\Models\DailyProduction;
use App\Models\ProductionSchedule;
use App\Models\SewingLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionScheduleService
{
    public function create(array $data): ProductionSchedule
    {
        return DB::transaction(function () use ($data) {
            $line = SewingLine::query()
                ->whereKey($data['sewing_line_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $start = Carbon::parse($data['start_date'])->startOfDay();
            $finish = Carbon::parse($data['finish_date'])->startOfDay();
            $totalTarget = (int) $data['total_target'];

            if ($finish->lt($start)) {
                throw ValidationException::withMessages([
                    'finish_date' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
                ]);
            }

            if ($totalTarget < 1) {
                throw ValidationException::withMessages([
                    'total_target' => 'Total target harus lebih dari nol.',
                ]);
            }

            $overlap = ProductionSchedule::query()
                ->where('sewing_line_id', $line->id)
                ->whereDate('start_date', '<=', $finish->toDateString())
                ->whereDate('finish_date', '>=', $start->toDateString())
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'start_date' => 'Schedule bertabrakan dengan schedule lain pada sewing line ini.',
                ]);
            }

            $schedule = ProductionSchedule::create([
                'sewing_line_id' => $line->id,
                'production_order_id' => $data['production_order_id'],
                'start_date' => $start->toDateString(),
                'finish_date' => $finish->toDateString(),
                'total_target' => $totalTarget,
                'status' => 'planned',
            ]);

            $days = $start->diffInDays($finish) + 1;
            $baseTarget = intdiv($totalTarget, $days);
            $remainder = $totalTarget % $days;

            $rows = [];

            for ($i = 0; $i < $days; $i++) {
                $date = $start->copy()->addDays($i);
                $target = $baseTarget;

                // Sisa pembagian ditempatkan pada hari terakhir.
                if ($i === $days - 1) {
                    $target += $remainder;
                }

                $rows[] = [
                    'production_schedule_id' => $schedule->id,
                    'production_date' => $date->toDateString(),
                    'base_target_qty' => $target,
                    'target_qty' => $target,
                    'actual_qty' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DailyProduction::insert($rows);

            return $schedule->load([
                'sewingLine',
                'productionOrder',
                'dailyProductions',
            ]);
        }, 3);
    }

    public function shiftFollowingSchedules(
        int $sewingLineId,
        string $afterDate,
        int $days = 1
    ): void {
        if ($days < 1) {
            return;
        }

        $schedules = ProductionSchedule::query()
            ->where('sewing_line_id', $sewingLineId)
            ->whereDate('start_date', '>', $afterDate)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->get();

        foreach ($schedules as $schedule) {
            $dailyRows = DailyProduction::query()
                ->where('production_schedule_id', $schedule->id)
                ->orderByDesc('production_date')
                ->lockForUpdate()
                ->get();

            foreach ($dailyRows as $daily) {
                $daily->production_date = Carbon::parse(
                    $daily->production_date
                )->addDays($days)->toDateString();

                $daily->save();
            }

            $schedule->start_date = Carbon::parse($schedule->start_date)
                ->addDays($days)->toDateString();

            $schedule->finish_date = Carbon::parse($schedule->finish_date)
                ->addDays($days)->toDateString();

            $schedule->save();
        }
    }
}
