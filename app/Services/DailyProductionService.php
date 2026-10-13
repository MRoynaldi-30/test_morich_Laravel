<?php

namespace App\Services;

use App\Models\DailyProduction;
use App\Models\ProductionSchedule;
use App\Models\SewingLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DailyProductionService
{
    public function __construct(
        private readonly ProductionScheduleService $scheduleService
    ) {}

    public function recordActual(
        int $dailyProductionId,
        int $actualQty
    ): ProductionSchedule {
        if ($actualQty < 0) {
            throw ValidationException::withMessages([
                'actual_qty' => 'Aktual produksi tidak boleh negatif.',
            ]);
        }

        return DB::transaction(function () use (
            $dailyProductionId,
            $actualQty
        ) {
            // Baca relasi awal untuk menentukan sewing line yang harus dikunci.
            $scheduleId = DailyProduction::query()
                ->whereKey($dailyProductionId)
                ->value('production_schedule_id');

            if (!$scheduleId) {
                throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())
                    ->setModel(DailyProduction::class, [$dailyProductionId]);
            }

            $lineId = ProductionSchedule::query()
                ->whereKey($scheduleId)
                ->value('sewing_line_id');

            if (!$lineId) {
                throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())
                    ->setModel(ProductionSchedule::class, [$scheduleId]);
            }

            // Urutan lock harus konsisten: line -> schedule -> detail harian.
            SewingLine::query()
                ->whereKey($lineId)
                ->lockForUpdate()
                ->firstOrFail();

            $schedule = ProductionSchedule::query()
                ->whereKey($scheduleId)
                ->lockForUpdate()
                ->firstOrFail();

            $daily = DailyProduction::query()
                ->whereKey($dailyProductionId)
                ->where('production_schedule_id', $schedule->id)
                ->lockForUpdate()
                ->firstOrFail();

            $daily->actual_qty = $actualQty;
            $daily->save();

            $this->rebalance($schedule, $daily->production_date);

            return $schedule->fresh([
                'sewingLine',
                'productionOrder',
                'dailyProductions',
            ]);
        }, 3);
    }

    /**
     * Hitung ulang target berdasarkan aktual yang sedang diproses.
     *
     * Kekurangan diteruskan ke hari berikutnya, tetapi aktual hari-hari
     * mendatang yang belum diinput tidak dianggap sebagai kekurangan final.
     * Hari tambahan hanya dibuat ketika hari terakhir sudah diinput aktual
     * dan masih memiliki kekurangan.
     */
    private function rebalance(
        ProductionSchedule $schedule,
        mixed $inputDate
    ): void {
        $start = Carbon::parse($schedule->start_date)->startOfDay();
        $finish = Carbon::parse($schedule->finish_date)->startOfDay();
        $actualDate = Carbon::parse($inputDate)->startOfDay();

        if ($actualDate->lt($start)) {
            throw ValidationException::withMessages([
                'actual_qty' => 'Tanggal aktual berada di luar schedule.',
            ]);
        }

        // Ambil detail harian sekaligus, bukan query satu per satu.
        $days = DailyProduction::query()
            ->where('production_schedule_id', $schedule->id)
            ->orderBy('production_date')
            ->lockForUpdate()
            ->get()
            ->keyBy(
                fn(DailyProduction $day) =>
                Carbon::parse($day->production_date)->toDateString()
            );

        $carry = 0;
        $date = $start->copy();

        // Hitung ulang dari awal sampai tanggal aktual yang disimpan.
        while ($date->lte($actualDate)) {
            $dateString = $date->toDateString();

            $daily = $days->get($dateString);

            if (!$daily) {
                // Perbaikan defensif jika detail tanggal ternyata belum ada.
                $daily = DailyProduction::create([
                    'production_schedule_id' => $schedule->id,
                    'production_date' => $dateString,
                    'base_target_qty' => 0,
                    'target_qty' => 0,
                    'actual_qty' => 0,
                ]);

                $days->put($dateString, $daily);
            }

            $baseTarget = (int) $daily->base_target_qty;
            $target = $baseTarget + $carry;

            if ((int) $daily->target_qty !== $target) {
                $daily->target_qty = $target;
                $daily->save();
            }

            $actual = (int) ($daily->actual_qty ?? 0);

            $carry = max(0, $target - $actual);

            $date->addDay();
        }

        /*
         * Kekurangan dari tanggal yang baru diinput hanya ditambahkan
         * ke target hari berikutnya. Hari berikutnya belum dianggap
         * gagal produksi sebelum aktualnya benar-benar diinput.
         */
        $nextDate = $actualDate->copy()->addDay();

        if ($nextDate->lte($finish)) {
            $nextString = $nextDate->toDateString();
            $nextDaily = $days->get($nextString);

            if ($nextDaily) {
                $nextTarget = (int) $nextDaily->base_target_qty + $carry;

                if ((int) $nextDaily->target_qty !== $nextTarget) {
                    $nextDaily->target_qty = $nextTarget;
                    $nextDaily->save();
                }
            }
        } elseif ($actualDate->greaterThanOrEqualTo($finish) && $carry > 0) {
            /*
             * Hari terakhir sudah diinput, tetapi target belum tercapai.
             * Tambah tepat satu hari agar proses tidak berjalan tanpa akhir.
             */
            $oldFinish = $finish->toDateString();

            $this->scheduleService->shiftFollowingSchedules(
                $schedule->sewing_line_id,
                $oldFinish,
                1
            );

            $newDate = $finish->copy()->addDay();

            $newDaily = DailyProduction::create([
                'production_schedule_id' => $schedule->id,
                'production_date' => $newDate->toDateString(),
                'base_target_qty' => 0,
                'target_qty' => $carry,
                'actual_qty' => 0,
            ]);

            $schedule->finish_date = $newDate->toDateString();
            $schedule->status = 'delayed';
            $schedule->save();

            $days->put($newDate->toDateString(), $newDaily);
        }

        $this->refreshStatus($schedule, $actualDate);
    }

    private function refreshStatus(
        ProductionSchedule $schedule,
        Carbon $actualDate
    ): void {
        $days = DailyProduction::query()
            ->where('production_schedule_id', $schedule->id)
            ->get();

        $allTargetsMet = $days->isNotEmpty()
            && $days->every(
                fn(DailyProduction $day) =>
                (int) ($day->actual_qty ?? 0) >= (int) $day->target_qty
            );

        if ($allTargetsMet) {
            $schedule->status = 'completed';
        } elseif (
            Carbon::parse($schedule->finish_date)->gt($actualDate)
            && $actualDate->greaterThanOrEqualTo(
                Carbon::parse($schedule->start_date)
            )
        ) {
            $schedule->status = 'in_progress';
        } else {
            $schedule->status = 'delayed';
        }

        $schedule->save();
    }
}
