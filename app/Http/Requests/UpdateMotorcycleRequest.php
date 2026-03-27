<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMotorcycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'make'            => ['required', 'string', 'max:100'],
            'model'           => ['required', 'string', 'max:100'],
            'year'            => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'plate'           => ['nullable', 'string', 'max:20'],
            'initial_mileage' => ['required', 'integer', 'min:0'],
            'photo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }
}
