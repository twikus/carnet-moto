<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\InvoiceExtractionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessInvoiceJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Invoice $invoice) {}

    public function handle(InvoiceExtractionService $extractionService): void
    {
        $extractionService->extract($this->invoice);
    }
}
