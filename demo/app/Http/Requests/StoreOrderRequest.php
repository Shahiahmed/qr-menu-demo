<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a guest order. Only the shape is checked here — availability and
 * pricing are re-resolved from the database in the controller, never trusted
 * from the client.
 */
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.id' => ['required', 'integer', 'exists:dishes,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],

            'table_number' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'comment' => ['nullable', 'string', 'max:500'],
            'locale' => ['nullable', 'string', 'in:ru,kk'],
        ];
    }
}
