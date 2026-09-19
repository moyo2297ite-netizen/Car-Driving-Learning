<?php

namespace App\Http\Requests;

use App\Enums\ContentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:content_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(ContentType::class)],
            'url' => ['required', 'string', 'max:2048'],
            'order_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
