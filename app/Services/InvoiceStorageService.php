<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Motorcycle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class InvoiceStorageService
{
    public function store(UploadedFile $file, Motorcycle $motorcycle): Invoice
    {
        $directory = 'invoices/' . $motorcycle->id;

        // Stockage temporaire de l'original
        $originalPath = $file->store($directory, 'public');

        // Compression et remplacement par un JPEG optimisé
        $compressedPath = $this->compress($originalPath);

        return Invoice::create([
            'path'              => $compressedPath,
            'original_filename' => $file->getClientOriginalName(),
            'extraction_status' => 'pending',
        ]);
    }

    public function delete(Invoice $invoice): void
    {
        Storage::disk('public')->delete($invoice->path);
        $invoice->delete();
    }

    private function compress(string $originalPath): string
    {
        $absolutePath = Storage::disk('public')->path($originalPath);

        $manager = new ImageManager(new Driver());
        $image = $manager->decodePath($absolutePath);

        // Redimensionnement si plus large que 2000px (conserve les proportions)
        $image->scaleDown(width: 2000);

        // Sauvegarde en JPEG qualité 80
        $compressedPath = preg_replace('/\.[^.]+$/', '.jpg', $originalPath);
        $absoluteCompressedPath = Storage::disk('public')->path($compressedPath);

        $image->encode(new JpegEncoder(quality: 80))->save($absoluteCompressedPath);

        // Suppression de l'original si l'extension a changé (PNG → JPG)
        if ($originalPath !== $compressedPath) {
            Storage::disk('public')->delete($originalPath);
        }

        return $compressedPath;
    }
}
