<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_qty' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'actual_qty.required' => 'Aktual produksi wajib diisi.',
            'actual_qty.integer' => 'Aktual produksi harus berupa angka bulat.',
            'actual_qty.min' => 'Aktual produksi tidak boleh negatif.',
        ];
    }
}