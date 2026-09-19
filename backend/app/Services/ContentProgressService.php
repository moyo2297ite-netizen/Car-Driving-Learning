<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\Content;
use App\Models\Quiz;
use App\Models\StudentContentProgress;
use App\Models\User;
use Illuminate\Support\Collection;

class ContentProgressService
{
    public function canAccess(User $student, Content $content): bool
    {
        $this->ensureStudent($student);

        $category = $content->category;

        if (! $category->is_sequential) {
            return true;
        }

        $previousContents = Content::query()
            ->where('category_id', $category->id)
            ->where('order_index', '<', $content->order_index)
            ->orderBy('order_index')
            ->get();

        foreach ($previousContents as $previous) {
            if (! $this->isContentUnlocked($student, $previous)) {
                return false;
            }
        }

        return true;
    }

    public function markViewed(User $student, Content $content): StudentContentProgress
    {
        if (! $this->canAccess($student, $content)) {
            throw new DomainException('يجب إكمال المحتوى السابق أولاً.');
        }

        return StudentContentProgress::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'content_id' => $content->id,
            ],
            ['completed' => true],
        );
    }

    public function submitQuiz(User $student, Quiz $quiz, array $answers): array
    {
        $content = $quiz->content;

        if (! $this->canAccess($student, $content)) {
            throw new DomainException('يجب إكمال المحتوى السابق أولاً.');
        }

        $questions = $quiz->questions;
        $total = $questions->count();

        if ($total === 0) {
            throw new DomainException('الكويز لا يحتوي أسئلة.');
        }

        $correct = 0;

        foreach ($questions as $question) {
            $given = $answers[$question->id] ?? null;

            if ($given === $question->correct_answer) {
                $correct++;
            }
        }

        $score = (int) round(($correct / $total) * 100);
        $passed = $score >= $quiz->pass_score;

        StudentContentProgress::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'content_id' => $content->id,
            ],
            [
                'completed' => $passed || ! $content->category->is_sequential,
                'quiz_score' => $score,
            ],
        );

        return [
            'score' => $score,
            'passed' => $passed,
            'pass_score' => $quiz->pass_score,
            'blocks_next' => $content->category->is_sequential && ! $passed,
        ];
    }

    public function studentProgressReport(User $student): Collection
    {
        return StudentContentProgress::query()
            ->with(['content.category', 'content.quiz'])
            ->where('student_id', $student->id)
            ->get();
    }

    private function isContentUnlocked(User $student, Content $content): bool
    {
        $progress = StudentContentProgress::query()
            ->where('student_id', $student->id)
            ->where('content_id', $content->id)
            ->first();

        if (! $progress?->completed) {
            return false;
        }

        $quiz = $content->quiz;

        if ($quiz && $content->category->is_sequential) {
            return ($progress->quiz_score ?? 0) >= $quiz->pass_score;
        }

        return true;
    }

    private function ensureStudent(User $user): void
    {
        if (! $user->isStudent()) {
            throw new DomainException('هذه العملية متاحة للطلاب فقط.');
        }
    }
}
