<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentSkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'skill' => new SkillResource($this->whenLoaded('skill')),
            'is_completed' => $this->is_completed,
            'updated_by' => new UserResource($this->whenLoaded('updatedBy')),
            'skill_updated_at' => $this->skill_updated_at,
        ];
    }
}
