<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking' => new BookingResource($this->whenLoaded('booking')),
            'method' => $this->method->value,
            'amount' => $this->amount,
            'status' => $this->status->value,
            'confirmed_by' => new UserResource($this->whenLoaded('confirmedBy')),
            'created_at' => $this->created_at,
        ];
    }
}
