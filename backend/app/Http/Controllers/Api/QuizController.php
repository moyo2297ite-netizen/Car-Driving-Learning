<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\SubmitQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Content;
use App\Models\Quiz;
use App\Services\ContentProgressService;

class QuizController extends Controller
{
    public function __construct(private readonly ContentProgressService $progress) {}

    public function store(StoreQuizRequest $request, Content $content)
    {
        $quiz = Quiz::query()->create([
            'content_id' => $content->id,
            'pass_score' => $request->pass_score,
        ]);

        foreach ($request->questions as $question) {
            $quiz->questions()->create([
                'question' => $question['question'],
                'options' => $question['options'],
                'correct_answer' => $question['correct_answer'],
            ]);
        }

        return new QuizResource($quiz->load('questions'));
    }

    public function submit(SubmitQuizRequest $request, Quiz $quiz)
    {
        $result = $this->progress->submitQuiz($request->user(), $quiz, $request->answers);

        return response()->json($result);
    }
}
