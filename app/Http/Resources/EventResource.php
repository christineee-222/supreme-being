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
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'status' => $this->status,
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'user_id' => $this->user_id,
            'essence_numen_id' => $this->essence_numen_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'rsvp' => $this->whenLoaded('rsvpForViewer', function () {
                return $this->rsvpForViewer
                    ? new EventRsvpResource($this->rsvpForViewer)
                    : null;
            }),
        ];
    }
}

