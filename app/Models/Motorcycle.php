<?php

namespace App\Models;

use Database\Factories\MotorcycleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Motorcycle extends Model
{
    /** @use HasFactory<MotorcycleFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'make',
        'model',
        'year',
        'plate',
        'initial_mileage',
        'photo_path',
    ];

    protected $casts = [
        'year'            => 'integer',
        'initial_mileage' => 'integer',
    ];
}
