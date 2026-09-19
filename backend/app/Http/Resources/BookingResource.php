<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student' => new UserResource($this->whenLoaded('student')),
            'slot' => new AvailabilitySlotResource($this->whenLoaded('slot')),
            'assigned_by' => new UserResource($this->whenLoaded('assignedBy')),
            'price' => $this->price,
            'status' => $this->status->value,
            'cancelled_by' => new UserResource($this->whenLoaded('cancelledBy')),
            'penalty_amount' => $this->penalty_amount,
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at,
        ];
    }
}
