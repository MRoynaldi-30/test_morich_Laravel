<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyProductionRequest;
use App\Models\DailyProduction;
use App\Services\DailyProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class DailyProductionController extends Controller
{
    public function store(
        StoreDailyProductionRequest $request,
        int $dailyProductionId,
        DailyProductionService $service
    ): JsonResponse {
        $startedAt = microtime(true);

        Log::info('Actual output: proses dimulai', [
            'daily_production_id' => $dailyProductionId,
        ]);

        try {
            $actualQty = (int) $request->validated('actual_qty');

            $schedule = $service->recordActual(
                $dailyProductionId,
                $actualQty
            );

            $duration = round(microtime(true) - $startedAt, 3);

            Log::info('Actual output: proses selesai', [
                'daily_production_id' => $dailyProductionId,
                'actual_qty' => $actualQty,
                'duration_seconds' => $duration,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aktual produksi berhasil disimpan dan balancing selesai.',
                'duration_seconds' => $duration,
                'data' => $schedule,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Actual output: proses gagal', [
                'daily_production_id' => $dailyProductionId,
                'duration_seconds' => round(
                    microtime(true) - $startedAt,
                    3
                ),
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Aktual produksi gagal disimpan. Silakan coba kembali.',
            ], 500);
        }
    }

    public function index(int $scheduleId): JsonResponse
    {
        $days = DailyProduction::query()
            ->where('production_schedule_id', $scheduleId)
            ->orderBy('production_date')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data produksi harian berhasil diambil.',
            'data' => $days,
        ]);
    }
}