<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'uploader' => new UserResource($this->whenLoaded('uploader')),
            'title' => $this->title,
            'type' => $this->type->value,
            'url' => $this->url,
            'order_index' => $this->order_index,
            'quiz' => new QuizResource($this->whenLoaded('quiz')),
            'created_at' => $this->created_at,
        ];
    }
}
