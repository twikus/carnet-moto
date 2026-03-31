<?php

namespace App\Services;

use App\Ai\Agents\InvoiceExtractorAgent;
use App\Jobs\ProcessInvoiceJob;
use App\Models\Invoice;
use App\Models\Maintenance;
use App\Models\Motorcycle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\LocalImage;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class InvoiceExtractionService
{
    public function confirm(Invoice $invoice, array $data): Maintenance
    {
        $motorcycle = Motorcycle::first();

        $maintenance = Maintenance::create([
            'motorcycle_id' => $motorcycle->id,
            'performed_at'  => $data['performed_at'],
            'mileage'       => $data['mileage'],
            'garage'        => $data['garage'] ?? null,
            'total_amount'  => $data['total_amount'] ?? null,
            'notes'         => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $maintenance->maintenanceItems()->create([
                'label'  => $item['label'],
                'amount' => $item['amount'] ?? null,
            ]);
        }

        $invoice->update(['maintenance_id' => $maintenance->id]);

        return $maintenance;
    }

    public function retry(Invoice $invoice): void
    {
        $invoice->update(['extraction_status' => 'pending', 'extracted_data' => null]);

        ProcessInvoiceJob::dispatch($invoice);
    }

    public function extract(Invoice $invoice): void
    {
        $invoice->update(['extraction_status' => 'processing']);

        try {
            $absolutePath = Storage::disk('local')->path($invoice->path);
            $image = new LocalImage($absolutePath, 'image/jpeg');

            /** @var StructuredAgentResponse $response */
            $response = InvoiceExtractorAgent::make()->prompt(
                'Extrais les informations de cette facture de moto.',
                [$image]
            );

            $invoice->update([
                'extracted_data'    => $response->toArray(),
                'extraction_status' => 'done',
            ]);
        } catch (Throwable $e) {
            Log::error('Invoice extraction failed', [
                'invoice_id' => $invoice->id,
                'error'      => $e->getMessage(),
            ]);

            $invoice->update(['extraction_status' => 'failed']);
        }
    }
}
