<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'device_name',
        'action',
        'type',
        'user_or_source',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
