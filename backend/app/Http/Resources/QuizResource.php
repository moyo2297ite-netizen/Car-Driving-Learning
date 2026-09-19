<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pass_score' => $this->pass_score,
            'questions' => QuizQuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
