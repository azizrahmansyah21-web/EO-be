<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'event_id',
        'sales_id',
        'name',
        'company',
        'title',
        'phone',
        'vip',
        'vip_tier',
        'pax',
        'status',
        'claimed_at',
        'souvenir_claimed',
        'souvenir_claimed_at',
        'snack_claimed',
        'snack_claimed_at',
        'car_model',
    ];

    protected function casts(): array
    {
        return [
            'vip' => 'boolean',
            'pax' => 'integer',
            'claimed_at' => 'datetime',
            'souvenir_claimed' => 'boolean',
            'souvenir_claimed_at' => 'datetime',
            'snack_claimed' => 'boolean',
            'snack_claimed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically assign a UUID token if not provided
        static::creating(function ($guest) {
            if (empty($guest->token)) {
                $guest->token = (string) Str::uuid();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }
}
