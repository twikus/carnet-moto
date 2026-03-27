<?php

namespace App\Models;

use Database\Factories\MileageLogFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MileageLog extends Model
{
    /** @use HasFactory<MileageLogFactory> */
    use HasFactory, HasUuids;

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'motorcycle_id',
        'mileage',
        'logged_at',
    ];

    protected $casts = [
        'mileage'    => 'integer',
        'logged_at'  => 'date',
        'created_at' => 'datetime',
    ];

    public function motorcycle(): BelongsTo
    {
        return $this->belongsTo(Motorcycle::class);
    }
}
