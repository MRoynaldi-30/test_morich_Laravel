<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sewing_line_id' => [
                'required',
                'integer',
                'exists:sewing_lines,id',
            ],
            'production_order_id' => [
                'required',
                'integer',
                'exists:production_orders,id',
            ],
            'start_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'finish_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
            'total_target' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sewing_line_id.required' => 'Sewing line wajib dipilih.',
            'sewing_line_id.exists' => 'Sewing line tidak ditemukan.',
            'production_order_id.required' => 'Order wajib dipilih.',
            'production_order_id.exists' => 'Order tidak ditemukan.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'finish_date.after_or_equal' =>
                'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'total_target.min' => 'Total target minimal 1 pcs.',
        ];
    }
}