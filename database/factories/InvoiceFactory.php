<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'maintenance_id'    => null,
            'path'              => 'invoices/' . $this->faker->uuid() . '.jpg',
            'original_filename' => $this->faker->word() . '.jpg',
            'sort_order'        => 0,
            'extraction_status' => 'pending',
            'extracted_data'    => null,
        ];
    }
}
