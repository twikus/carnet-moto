<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Maintenance;
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
            'maintenance_id'    => Maintenance::factory(),
            'file_path'         => 'invoices/' . $this->faker->uuid() . '.jpg',
            'original_filename' => $this->faker->word() . '.jpg',
            'extraction_status' => 'pending',
            'extracted_data'    => null,
        ];
    }
}
