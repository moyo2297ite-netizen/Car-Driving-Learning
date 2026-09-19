<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_sequential' => $this->is_sequential,
            'contents' => ContentResource::collection($this->whenLoaded('contents')),
            'created_at' => $this->created_at,
        ];
    }
}
