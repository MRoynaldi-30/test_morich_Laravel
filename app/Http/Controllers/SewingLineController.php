<?php

namespace App\Http\Controllers;

use App\Models\SewingLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SewingLineController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SewingLine::query()
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                'unique:sewing_lines,code',
            ],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $line = SewingLine::create([
            ...$data,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sewing line berhasil ditambahkan.',
            'data' => $line,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $line = SewingLine::findOrFail($id);

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('sewing_lines', 'code')->ignore($line->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);

        $line->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Sewing line berhasil diperbarui.',
            'data' => $line,
        ]);
    }
}