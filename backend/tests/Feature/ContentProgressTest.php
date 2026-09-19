<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_sequential_category_blocks_content_until_previous_completed(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);

        $category = ContentCategory::query()->create(['name' => 'Sequential', 'is_sequential' => true]);

        $first = Content::query()->create([
            'category_id' => $category->id,
            'uploaded_by' => $supervisor->id,
            'title' => 'First',
            'type' => 'video',
            'url' => 'https://example.com/1.mp4',
            'order_index' => 0,
        ]);

        $second = Content::query()->create([
            'category_id' => $category->id,
            'uploaded_by' => $supervisor->id,
            'title' => 'Second',
            'type' => 'video',
            'url' => 'https://example.com/2.mp4',
            'order_index' => 1,
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson("/api/contents/{$second->id}")
            ->assertForbidden();

        $this->actingAs($student, 'sanctum')
            ->postJson("/api/contents/{$first->id}/view")
            ->assertOk()
            ->assertJsonPath('completed', true);

        $this->actingAs($student, 'sanctum')
            ->getJson("/api/contents/{$second->id}")
            ->assertOk();
    }

    public function test_non_sequential_category_allows_free_access(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);

        $category = ContentCategory::query()->create(['name' => 'Free', 'is_sequential' => false]);

        $second = Content::query()->create([
            'category_id' => $category->id,
            'uploaded_by' => $supervisor->id,
            'title' => 'Any order',
            'type' => 'image',
            'url' => 'https://example.com/img.png',
            'order_index' => 5,
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson("/api/contents/{$second->id}")
            ->assertOk();
    }

    public function test_quiz_correct_answer_hidden_from_students_but_visible_to_staff(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);

        $category = ContentCategory::query()->create(['name' => 'Cat', 'is_sequential' => false]);
        $content = Content::query()->create([
            'category_id' => $category->id,
            'uploaded_by' => $supervisor->id,
            'title' => 'C',
            'type' => 'video',
            'url' => 'https://example.com/c.mp4',
            'order_index' => 0,
        ]);

        $this->actingAs($supervisor, 'sanctum')->postJson("/api/contents/{$content->id}/quiz", [
            'pass_score' => 50,
            'questions' => [
                ['question' => 'Q1', 'options' => ['a', 'b'], 'correct_answer' => 'a'],
            ],
        ])->assertCreated();

        $studentView = $this->actingAs($student, 'sanctum')
            ->getJson("/api/contents/{$content->id}")
            ->assertOk();

        $this->assertArrayNotHasKey('correct_answer', $studentView->json('data.quiz.questions.0'));

        $staffView = $this->actingAs($supervisor, 'sanctum')
            ->getJson("/api/contents/{$content->id}")
            ->assertOk();

        $this->assertEquals('a', $staffView->json('data.quiz.questions.0.correct_answer'));
    }
}
