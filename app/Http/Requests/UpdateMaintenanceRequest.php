<?php

namespace App\Http\Requests;

use App\Rules\MileageChronologyRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maintenance = $this->route('maintenance');

        return [
            'performed_at'       => ['required', 'date'],
            'mileage'            => ['required', 'integer', 'min:0', new MileageChronologyRule($maintenance->motorcycle->maintenances(), $maintenance->id)],
            'garage'             => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.label'      => ['required', 'string', 'max:255'],
            'items.*.amount'     => ['nullable', 'numeric', 'min:0'],
            'update_mileage_log' => ['boolean'],
        ];
    }
}
