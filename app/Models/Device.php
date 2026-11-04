<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'name',
        'type',
        'is_on',
        'value',
        'settings',
        'power_watts',
        'is_online',
        'battery_percent',
    ];

    protected $casts = [
        'is_on' => 'boolean',
        'is_online' => 'boolean',
        'settings' => 'array',
        'value' => 'integer',
        'power_watts' => 'float',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
