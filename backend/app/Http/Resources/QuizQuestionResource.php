<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hideAnswer = $request->user()?->isStudent() ?? true;

        return [
            'id' => $this->id,
            'question' => $this->question,
            'options' => $this->options,
            'correct_answer' => $this->when(! $hideAnswer, $this->correct_answer),
        ];
    }
}
