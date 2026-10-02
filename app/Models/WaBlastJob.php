<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaBlastJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'guest_id',
        'phone_target',
        'message_content',
        'status',
        'retry_count',
        'sent_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'retry_count' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
