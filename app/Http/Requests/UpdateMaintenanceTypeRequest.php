<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:100'],
            'interval_km'           => ['nullable', 'integer', 'min:1'],
            'interval_days'         => ['nullable', 'integer', 'min:1'],
            'alert_threshold_km'    => ['nullable', 'integer', 'min:0'],
            'alert_threshold_days'  => ['nullable', 'integer', 'min:0'],
            'is_active'             => ['boolean'],
        ];
    }
}
