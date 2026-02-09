<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'status',
        'user_id',
        'essence_numen_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Relationships
     */
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

    /**
     * Used by API to eager-load the RSVP
     * for the currently authenticated user.
     */
    public function rsvpForViewer(): HasOne
    {
        return $this->hasOne(EventRsvp::class);
    }

    /**
     * Domain helpers (used by policies)
     */
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

    /**
     * Model boot logic
     */
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
