<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Jobs\ProcessInvoiceJob;
use App\Models\Invoice;
use App\Models\Motorcycle;
use App\Services\InvoiceStorageService;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceStorageService $storageService) {}

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
}
