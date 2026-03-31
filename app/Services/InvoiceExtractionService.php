<?php

namespace App\Services;

use App\Ai\Agents\InvoiceExtractorAgent;
use App\Jobs\ProcessInvoiceJob;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\LocalImage;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class InvoiceExtractionService
{
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
