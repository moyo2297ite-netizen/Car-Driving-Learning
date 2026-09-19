<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentContentProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student' => new UserResource($this->whenLoaded('student')),
            'content' => new ContentResource($this->whenLoaded('content')),
            'completed' => $this->completed,
            'quiz_score' => $this->quiz_score,
            'updated_at' => $this->updated_at,
        ];
    }
}
