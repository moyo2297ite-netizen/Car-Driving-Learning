<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_returns_counts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);

        $slot = AvailabilitySlot::query()->create([
            'instructor_id' => $instructor->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'status' => 'approved',
        ]);

        $booking = Booking::query()->create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'price' => 20,
            'status' => 'completed',
        ]);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'method' => 'cash',
            'amount' => 20,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/dashboard/admin')
            ->assertOk();

        $this->assertEquals(1, $response->json('students_count'));
        $this->assertEquals(1, $response->json('instructors_count'));
        $this->assertEquals(1, $response->json('completed_bookings'));
        $this->assertEquals(20, $response->json('total_revenue'));
    }

    public function test_supervisor_dashboard_aggregates_pending_items(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);

        AvailabilitySlot::query()->create([
            'instructor_id' => $instructor->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'status' => 'proposed',
        ]);

        $response = $this->actingAs($supervisor, 'sanctum')
            ->getJson('/api/dashboard/supervisor')
            ->assertOk();

        $this->assertCount(1, $response->json('pending_slots'));
    }

    public function test_instructor_dashboard_shows_own_slots_only(): void
    {
        $instructorA = User::factory()->create(['role' => UserRole::Instructor]);
        $instructorB = User::factory()->create(['role' => UserRole::Instructor]);

        AvailabilitySlot::query()->create([
            'instructor_id' => $instructorA->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'status' => 'proposed',
        ]);

        AvailabilitySlot::query()->create([
            'instructor_id' => $instructorB->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'status' => 'proposed',
        ]);

        $response = $this->actingAs($instructorA, 'sanctum')
            ->getJson('/api/dashboard/instructor')
            ->assertOk();

        $this->assertCount(1, $response->json('my_slots'));
    }
}
