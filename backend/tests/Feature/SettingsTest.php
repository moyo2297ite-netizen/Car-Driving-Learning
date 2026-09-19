<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'lesson_price' => 25,
                'cancellation_grace_hours' => 12,
                'cancellation_penalty_percent' => 30,
            ])
            ->assertOk()
            ->assertJsonPath('lesson_price', 25)
            ->assertJsonPath('cancellation_grace_hours', 12)
            ->assertJsonPath('cancellation_penalty_percent', 30);
    }

    public function test_supervisor_can_view_but_not_update_settings(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);

        $this->actingAs($supervisor, 'sanctum')
            ->getJson('/api/settings')
            ->assertOk();

        $this->actingAs($supervisor, 'sanctum')
            ->putJson('/api/settings', ['lesson_price' => 99])
            ->assertForbidden();
    }

    public function test_instructor_cannot_view_settings(): void
    {
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);

        $this->actingAs($instructor, 'sanctum')
            ->getJson('/api/settings')
            ->assertForbidden();
    }
}
