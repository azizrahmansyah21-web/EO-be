<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'operator_id',
        'pos',
        'action',
        'device_info',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'pos' => 'integer',
            'scanned_at' => 'datetime',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
