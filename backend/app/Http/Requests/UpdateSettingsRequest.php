<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lesson_price' => ['sometimes', 'numeric', 'min:0'],
            'cancellation_grace_hours' => ['sometimes', 'integer', 'min:0'],
            'cancellation_penalty_percent' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'sham_cash_account' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
