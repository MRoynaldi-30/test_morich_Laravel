<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductionScheduleRequest;
use App\Models\ProductionSchedule;
use App\Services\ProductionScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductionScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $schedules = ProductionSchedule::query()
            ->with([
                'sewingLine:id,code,name',
                'productionOrder:id,order_number,style',
            ])
            ->when(
                $request->filled('sewing_line_id'),
                fn ($query) => $query->where(
                    'sewing_line_id',
                    $request->integer('sewing_line_id')
                )
            )
            ->orderBy('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data schedule berhasil diambil.',
            'data' => $schedules,
        ]);
    }

    public function store(
        StoreProductionScheduleRequest $request,
        ProductionScheduleService $service
    ): JsonResponse {
        try {
            $schedule = $service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Schedule produksi berhasil dibuat.',
                'data' => $schedule,
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Schedule gagal dibuat. Silakan coba kembali.',
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $schedule = ProductionSchedule::query()
            ->with([
                'sewingLine',
                'productionOrder',
                'dailyProductions',
            ])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail schedule berhasil diambil.',
            'data' => $schedule,
        ]);
    }
}