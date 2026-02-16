<?php

namespace App\Models;

use App\Casts\BinaryUuidFk;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\UsesBinaryUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Event extends Model
{
    use HasFactory, HasUniqueSlug, UsesBinaryUuidV7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'status',
        'user_id',
        'essence_numen_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => BinaryUuidFk::class,
            'essence_numen_id' => BinaryUuidFk::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function essenceNumen(): BelongsTo
    {
        return $this->belongsTo(EssenceNumen::class, 'essence_numen_id');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function rsvpForViewer(): HasOne
    {
        return $this->hasOne(EventRsvp::class)->where('user_id', Auth::user()?->binaryId());
    }

    public function hasStarted(): bool
    {
        return $this->starts_at !== null
            && Carbon::now()->greaterThanOrEqualTo($this->starts_at);
    }

    public function isUpcoming(): bool
    {
        return ! $this->hasStarted();
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    protected static function booted(): void
    {
        static::creating(function ($event) {
            if (! $event->status) {
                $event->status = 'scheduled';
            }

            if (! $event->essence_numen_id) {
                $essence = EssenceNumen::create([
                    'type' => 'event',
                ]);

                $event->essence_numen_id = $essence->id;
            }
        });
    }
}

