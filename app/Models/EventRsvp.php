<?php

namespace App\Models;

use App\Casts\BinaryUuidFk;
use App\Models\Concerns\UsesBinaryUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRsvp extends Model
{
    use HasFactory, UsesBinaryUuidV7;

    protected $fillable = [
        'user_id',
        'event_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => BinaryUuidFk::class,
            'event_id' => BinaryUuidFk::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
