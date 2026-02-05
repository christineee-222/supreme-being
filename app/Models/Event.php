<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\EssenceNumen;
use App\Models\EventRsvp;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'ends_at'   => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function essenceNumen()
    {
        return $this->belongsTo(EssenceNumen::class, 'essence_numen_id');
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

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    /**
     * Model boot logic
     */
    protected static function booted()
    {
        static::creating(function ($event) {
            // Default status
            if (! $event->status) {
                $event->status = 'scheduled';
            }

            // Auto-create EssenceNumen
            if (! $event->essence_numen_id) {
                $essence = EssenceNumen::create([
                    'type' => 'event',
                ]);

                $event->essence_numen_id = $essence->id;
            }
        });
    }
}



