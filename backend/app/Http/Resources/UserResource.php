<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role->value,
            'transmission_type' => $this->transmission_type?->value,
            'teaches_manual' => $this->when($this->role === UserRole::Instructor, (bool) $this->teaches_manual),
            'teaches_automatic' => $this->when($this->role === UserRole::Instructor, (bool) $this->teaches_automatic),
            'created_at' => $this->created_at,
        ];
    }
}
