<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_skill_catalog(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $skill = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/skills', ['name' => 'ركن متوازي'])
            ->assertCreated()
            ->json('data');

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/skills/{$skill['id']}", ['name' => 'ركن متوازي محدث'])
            ->assertOk()
            ->assertJsonPath('data.name', 'ركن متوازي محدث');

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/skills/{$skill['id']}")
            ->assertOk();

        $this->assertDatabaseMissing('skills', ['id' => $skill['id']]);
    }

    public function test_instructor_cannot_manage_skill_catalog(): void
    {
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);

        $this->actingAs($instructor, 'sanctum')
            ->postJson('/api/skills', ['name' => 'مهارة جديدة'])
            ->assertForbidden();
    }

    public function test_instructor_can_mark_student_skill_completed(): void
    {
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);
        $skill = Skill::query()->create(['name' => 'ركن متوازي']);

        $this->actingAs($instructor, 'sanctum')
            ->putJson("/api/students/{$student->id}/skills/{$skill->id}", ['is_completed' => true])
            ->assertCreated()
            ->assertJsonPath('data.is_completed', true);

        $this->assertDatabaseHas('student_skills', [
            'student_id' => $student->id,
            'skill_id' => $skill->id,
            'is_completed' => true,
            'updated_by' => $instructor->id,
        ]);
    }

    public function test_certificate_eligibility_requires_all_skills_completed(): void
    {
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);

        $skillOne = Skill::query()->create(['name' => 'مهارة 1']);
        $skillTwo = Skill::query()->create(['name' => 'مهارة 2']);

        $this->actingAs($instructor, 'sanctum')
            ->putJson("/api/students/{$student->id}/skills/{$skillOne->id}", ['is_completed' => true]);

        $this->actingAs($supervisor, 'sanctum')
            ->getJson("/api/students/{$student->id}/certificate-eligibility")
            ->assertOk()
            ->assertJsonPath('eligible', false);

        $this->actingAs($instructor, 'sanctum')
            ->putJson("/api/students/{$student->id}/skills/{$skillTwo->id}", ['is_completed' => true]);

        $this->actingAs($supervisor, 'sanctum')
            ->getJson("/api/students/{$student->id}/certificate-eligibility")
            ->assertOk()
            ->assertJsonPath('eligible', true);
    }
}
