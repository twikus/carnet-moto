<?php

namespace App\Models;

use Database\Factories\MaintenanceTypeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceType extends Model
{
    /** @use HasFactory<MaintenanceTypeFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'motorcycle_id',
        'name',
        'interval_km',
        'interval_days',
        'alert_threshold_km',
        'alert_threshold_days',
        'is_active',
    ];

    protected $casts = [
        'interval_km'          => 'integer',
        'interval_days'        => 'integer',
        'alert_threshold_km'   => 'integer',
        'alert_threshold_days' => 'integer',
        'is_active'            => 'boolean',
    ];

    public function motorcycle(): BelongsTo
    {
        return $this->belongsTo(Motorcycle::class);
    }

    public function maintenanceItems(): HasMany
    {
        return $this->hasMany(MaintenanceItem::class);
    }
}
