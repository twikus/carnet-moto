<?php

namespace App\Http\Requests;

use App\Models\Motorcycle;
use App\Rules\MileageChronologyRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $motorcycle = Motorcycle::first();

        return [
            'performed_at'   => ['required', 'date'],
            'mileage'        => ['required', 'integer', 'min:0', new MileageChronologyRule($motorcycle->maintenances())],
            'garage'         => ['nullable', 'string', 'max:255'],
            'total_amount'   => ['nullable', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.label'  => ['required', 'string', 'max:255'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
