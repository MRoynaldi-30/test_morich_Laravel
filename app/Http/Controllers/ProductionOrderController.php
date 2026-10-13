<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductionOrderController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ProductionOrder::query()
                ->orderBy('order_number')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_number' => [
                'required',
                'string',
                'max:50',
                'unique:production_orders,order_number',
            ],
            'style' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'total_order_qty' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $order = ProductionOrder::create([
            ...$data,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil ditambahkan.',
            'data' => $order,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $order = ProductionOrder::findOrFail($id);

        $data = $request->validate([
            'order_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('production_orders', 'order_number')
                    ->ignore($order->id),
            ],
            'style' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'total_order_qty' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $order->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil diperbarui.',
            'data' => $order,
        ]);
    }
}