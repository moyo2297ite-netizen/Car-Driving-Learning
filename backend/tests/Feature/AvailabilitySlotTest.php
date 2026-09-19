<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilitySlotTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_propose_slot_and_it_starts_as_proposed(): void
    {
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);

        $this->actingAs($instructor, 'sanctum')
            ->postJson('/api/availability-slots', [
                'start_time' => now()->addDay()->toDateTimeString(),
                'end_time' => now()->addDay()->addHour()->toDateTimeString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'proposed');
    }

    public function test_student_cannot_propose_slot(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/availability-slots', [
                'start_time' => now()->addDay()->toDateTimeString(),
                'end_time' => now()->addDay()->addHour()->toDateTimeString(),
            ])
            ->assertForbidden();
    }

    public function test_supervisor_can_reject_slot_with_reason(): void
    {
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);

        $slot = $this->actingAs($instructor, 'sanctum')
            ->postJson('/api/availability-slots', [
                'start_time' => now()->addDay()->toDateTimeString(),
                'end_time' => now()->addDay()->addHour()->toDateTimeString(),
            ])->json('data');

        $this->actingAs($supervisor, 'sanctum')
            ->postJson("/api/availability-slots/{$slot['id']}/reject", ['reason' => 'الوقت متعارض'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.rejection_reason', 'الوقت متعارض');
    }

    public function test_rejecting_without_reason_fails_validation(): void
    {
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);

        $slot = $this->actingAs($instructor, 'sanctum')
            ->postJson('/api/availability-slots', [
                'start_time' => now()->addDay()->toDateTimeString(),
                'end_time' => now()->addDay()->addHour()->toDateTimeString(),
            ])->json('data');

        $this->actingAs($supervisor, 'sanctum')
            ->postJson("/api/availability-slots/{$slot['id']}/reject", [])
            ->assertUnprocessable();
    }

    public function test_available_slots_can_be_filtered_by_transmission_type(): void
    {
        $manualInstructor = User::factory()->create([
            'role' => UserRole::Instructor,
            'teaches_manual' => true,
            'teaches_automatic' => false,
        ]);
        $automaticInstructor = User::factory()->create([
            'role' => UserRole::Instructor,
            'teaches_manual' => false,
            'teaches_automatic' => true,
        ]);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);

        foreach ([$manualInstructor, $automaticInstructor] as $instructor) {
            $slot = $this->actingAs($instructor, 'sanctum')
                ->postJson('/api/availability-slots', [
                    'start_time' => now()->addDay()->toDateTimeString(),
                    'end_time' => now()->addDay()->addHour()->toDateTimeString(),
                ])->json('data');

            $this->actingAs($supervisor, 'sanctum')
                ->postJson("/api/availability-slots/{$slot['id']}/approve");
        }

        $response = $this->actingAs($student, 'sanctum')
            ->getJson('/api/availability-slots/available?transmission_type=manual')
            ->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($manualInstructor->id, $response->json('data.0.instructor.id'));
    }
}
