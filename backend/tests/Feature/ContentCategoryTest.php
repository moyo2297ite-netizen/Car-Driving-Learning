<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_create_update_and_delete_category(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);

        $category = $this->actingAs($supervisor, 'sanctum')
            ->postJson('/api/content-categories', ['name' => 'فئة جديدة', 'is_sequential' => true])
            ->assertCreated()
            ->json('data');

        $this->actingAs($supervisor, 'sanctum')
            ->putJson("/api/content-categories/{$category['id']}", ['is_sequential' => false])
            ->assertOk()
            ->assertJsonPath('data.is_sequential', false);

        $this->actingAs($supervisor, 'sanctum')
            ->deleteJson("/api/content-categories/{$category['id']}")
            ->assertOk();

        $this->assertDatabaseMissing('content_categories', ['id' => $category['id']]);
    }

    public function test_student_can_view_but_not_create_categories(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/content-categories')
            ->assertOk();

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/content-categories', ['name' => 'محاولة', 'is_sequential' => false])
            ->assertForbidden();
    }

    public function test_instructor_cannot_manage_content(): void
    {
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);

        $this->actingAs($instructor, 'sanctum')
            ->postJson('/api/content-categories', ['name' => 'محاولة', 'is_sequential' => false])
            ->assertForbidden();
    }
}
