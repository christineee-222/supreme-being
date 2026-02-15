<?php

namespace App\Http\Resources;

use App\Models\EventRsvp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\Uid\Uuid;

/**
 * @mixin EventRsvp
 */
class EventRsvpResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,

            'status' => (string) $this->status,

            'user_id' => $this->user_id ? Uuid::fromBinary($this->user_id)->toRfc4122() : null,
            'event_id' => $this->event_id ? Uuid::fromBinary($this->event_id)->toRfc4122() : null,

            // Helpful for mobile sync/debugging (safe to add; non-breaking)
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
