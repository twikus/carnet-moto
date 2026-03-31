<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Motorcycle;
use Illuminate\Http\UploadedFile;

class InvoiceStorageService
{
    public function store(UploadedFile $file, Motorcycle $motorcycle): Invoice
    {
        $directory = 'invoices/' . $motorcycle->id;
        $path = $file->store($directory, 'private');

        return Invoice::create([
            'path'              => $path,
            'original_filename' => $file->getClientOriginalName(),
            'extraction_status' => 'pending',
        ]);
    }
}
