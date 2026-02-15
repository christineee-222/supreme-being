<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\BinaryUuidFk;
use App\Models\Concerns\UsesBinaryUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Donation extends Model
{
    use HasFactory, UsesBinaryUuidV7;

    protected $fillable = [
        'amount',
        'currency',
        'status',
        'user_id',
        'essence_numen_id',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_webhook_event_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => BinaryUuidFk::class,
            'essence_numen_id' => BinaryUuidFk::class,
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

    protected static function booted(): void
    {
        self::creating(function (Donation $donation): void {
            if (! $donation->essence_numen_id) {
                $essence = EssenceNumen::create([
                    'type' => 'donation',
                ]);

                $donation->essence_numen_id = $essence->id;
            }
        });
    }
}
