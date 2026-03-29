<?php

namespace App\Models;

use Database\Factories\MotorcycleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'discord_webhook_url',
    ];

    protected $casts = [
        'year'            => 'integer',
        'initial_mileage' => 'integer',
    ];

    public function getCurrentMileageAttribute(): int
    {
        return $this->mileageLogs()->latest('logged_at')->value('mileage')
            ?? $this->initial_mileage;
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function maintenanceTypes(): HasMany
    {
        return $this->hasMany(MaintenanceType::class);
    }

    public function mileageLogs(): HasMany
    {
        return $this->hasMany(MileageLog::class);
    }

    public function alertLogs(): HasMany
    {
        return $this->hasMany(AlertLog::class);
    }
}
