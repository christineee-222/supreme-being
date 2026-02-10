<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Core event identity
            'id' => $this->id,
            'title' => (string) $this->title,
            'description' => $this->description ? (string) $this->description : null,

            // Dates (consistent ISO8601 for mobile parsing)
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),

            // Domain state
            'status' => $this->status,

            // Ownership / relations
            'user_id' => $this->user_id,
            'essence_numen_id' => $this->essence_numen_id,

            // Metadata timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            /*
            |--------------------------------------------------------------------------
            | RSVP for authenticated viewer
            |--------------------------------------------------------------------------
            | Only included when relationship is eager-loaded.
            | Prevents accidental extra queries.
            */
            'rsvp' => $this->whenLoaded('rsvpForViewer', function () {
                return $this->rsvpForViewer
                    ? new EventRsvpResource($this->rsvpForViewer)
                    : null;
            }),

            /*
            |--------------------------------------------------------------------------
            | Optional flattened RSVP status
            |--------------------------------------------------------------------------
            | Helpful for mobile lists/detail screens without needing
            | to parse nested resource objects.
            | Non-breaking because it's conditional.
            */
            'rsvp_status' => $this->when(
                isset($this->rsvp_status),
                $this->rsvp_status
            ),
        ];
    }
}


