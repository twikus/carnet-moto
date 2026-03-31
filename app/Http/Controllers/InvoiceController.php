<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Jobs\ProcessInvoiceJob;
use App\Models\Invoice;
use App\Models\Motorcycle;
use App\Services\InvoiceExtractionService;
use App\Services\InvoiceStorageService;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceStorageService $storageService,
        private readonly InvoiceExtractionService $extractionService,
    ) {}

    public function index(): Response
    {
        $invoices = Invoice::orderByDesc('created_at')->get()->map(fn (Invoice $invoice) => [
            'id'                => $invoice->id,
            'original_filename' => $invoice->original_filename,
            'extraction_status' => $invoice->extraction_status,
            'created_at'        => $invoice->created_at->toDateTimeString(),
            'summary'           => $invoice->extraction_status === 'done' ? [
                'performed_at' => $invoice->extracted_data['performed_at'] ?? null,
                'garage'       => $invoice->extracted_data['garage'] ?? null,
                'total_amount' => $invoice->extracted_data['total_amount'] ?? null,
                'confidence'   => $invoice->extracted_data['confidence'] ?? null,
            ] : null,
        ]);

        return Inertia::render('Invoice/Index', ['invoices' => $invoices]);
    }

    public function create(): Response
    {
        return Inertia::render('Invoice/Create');
    }

    public function store(StoreInvoiceRequest $request): \Illuminate\Http\RedirectResponse
    {
        $motorcycle = Motorcycle::first();

        $invoice = $this->storageService->store($request->file('photo'), $motorcycle);

        ProcessInvoiceJob::dispatch($invoice);

        return redirect()->route('invoice.processing', $invoice);
    }

    public function processing(Invoice $invoice): Response
    {
        return Inertia::render('Invoice/Processing', [
            'invoice' => $invoice->only('id', 'extraction_status'),
        ]);
    }

    public function retry(Invoice $invoice): \Illuminate\Http\RedirectResponse
    {
        $this->extractionService->retry($invoice);

        return redirect()->route('invoice.processing', $invoice);
    }

    public function destroy(Invoice $invoice): \Illuminate\Http\RedirectResponse
    {
        $this->storageService->delete($invoice);

        return redirect()->route('invoice.index');
    }

    public function review(Invoice $invoice): Response
    {
        // Formulaire de correction implémenté en SCRUM-12
        return Inertia::render('Invoice/Review', [
            'invoice' => $invoice->only('id', 'extraction_status', 'extracted_data', 'original_filename'),
        ]);
    }
}
