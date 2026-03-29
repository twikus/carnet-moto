<?php

namespace App\Models;

use Database\Factories\MaintenanceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    /** @use HasFactory<MaintenanceFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'motorcycle_id',
        'mileage',
        'performed_at',
        'garage',
        'total_amount',
        'notes',
        'ai_extraction_status',
    ];

    protected $casts = [
        'mileage'      => 'integer',
        'performed_at' => 'date',
        'total_amount' => 'decimal:2',
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
