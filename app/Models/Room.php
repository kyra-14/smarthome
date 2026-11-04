<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'floor',
        'ambient_temp',
        'ambient_humidity',
        'aqi',
    ];

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
