<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subtitle',
        'event_date',
        'event_time',
        'venue',
        'address',
        'maps_url',
        'image_url',
        'status',
        'quota_target',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'quota_target' => 'integer',
        ];
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function blastJobs(): HasMany
    {
        return $this->hasMany(WaBlastJob::class);
    }
}
