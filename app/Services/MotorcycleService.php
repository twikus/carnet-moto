<?php

namespace App\Services;

use App\Models\Motorcycle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MotorcycleService
{
    public function create(array $data, ?UploadedFile $photo): Motorcycle
    {
        return Motorcycle::create([
            'make'            => $data['make'],
            'model'           => $data['model'],
            'year'            => $data['year'],
            'plate'           => $data['plate'] ?? null,
            'initial_mileage' => $data['initial_mileage'],
            'photo_path'      => $photo ? $photo->store('motorcycles', 'public') : null,
        ]);
    }

    public function update(Motorcycle $motorcycle, array $data, ?UploadedFile $photo): Motorcycle
    {
        if ($photo) {
            if ($motorcycle->photo_path) {
                Storage::disk('public')->delete($motorcycle->photo_path);
            }
            $data['photo_path'] = $photo->store('motorcycles', 'public');
        }

        unset($data['photo']);

        $motorcycle->update($data);

        return $motorcycle;
    }
}
