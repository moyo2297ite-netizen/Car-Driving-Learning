<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function settings(): SettingService
    {
        $settings = app(SettingService::class);
        $settings->set(SettingService::LESSON_PRICE, 20);
        $settings->set(SettingService::CANCELLATION_GRACE_HOURS, 24);
        $settings->set(SettingService::CANCELLATION_PENALTY_PERCENT, 50);

        return $settings;
    }

    public function test_full_booking_lifecycle_confirm_and_complete(): void
    {
        $this->settings();

        $instructor = User::factory()->create([
            'role' => UserRole::Instructor,
            'teaches_manual' => true,
            'teaches_automatic' => true,
        ]);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'automatic']);

        $slot = $this->actingAs($instructor, 'sanctum')
            ->postJson('/api/availability-slots', [
                'start_time' => now()->addDays(2)->toDateTimeString(),
                'end_time' => now()->addDays(2)->addHour()->toDateTimeString(),
            ])->assertCreated()->json('data');

        $this->actingAs($supervisor, 'sanctum')
            ->postJson("/api/availability-slots/{$slot['id']}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $booking = $this->actingAs($student, 'sanctum')
            ->postJson('/api/bookings', ['slot_id' => $slot['id']])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.price', '20.00')
            ->json('data');

        $this->actingAs($supervisor, 'sanctum')
            ->postJson("/api/bookings/{$booking['id']}/assign", ['slot_id' => $slot['id']])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $this->actingAs($instructor, 'sanctum')
            ->postJson("/api/bookings/{$booking['id']}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_booking_rejects_transmission_mismatch(): void
    {
        $this->settings();

        $manualOnlyInstructor = User::factory()->create([
            'role' => UserRole::Instructor,
            'teaches_manual' => true,
            'teaches_automatic' => false,
        ]);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $automaticStudent = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'automatic']);

        $slot = AvailabilitySlot::query()->create([
            'instructor_id' => $manualOnlyInstructor->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'status' => 'approved',
        ]);

        $this->actingAs($automaticStudent, 'sanctum')
            ->postJson('/api/bookings', ['slot_id' => $slot->id])
            ->assertUnprocessable();
    }

    public function test_cancellation_within_grace_period_has_no_penalty(): void
    {
        $this->settings();

        [$booking] = $this->createConfirmedBooking(startsInHours: 48);

        $student = User::query()->find($booking->student_id);

        $response = $this->actingAs($student, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/cancel")
            ->assertOk();

        $this->assertNull($response->json('data.penalty_amount'));
    }

    public function test_cancellation_outside_grace_period_applies_penalty(): void
    {
        $this->settings();

        [$booking] = $this->createConfirmedBooking(startsInHours: 2);

        $student = User::query()->find($booking->student_id);

        $response = $this->actingAs($student, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/cancel")
            ->assertOk();

        $this->assertEquals('10.00', $response->json('data.penalty_amount'));
    }

    public function test_instructor_cannot_cancel_booking_directly(): void
    {
        $this->settings();

        [$booking, $instructor] = $this->createConfirmedBooking(startsInHours: 48);

        $this->actingAs($instructor, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/cancel")
            ->assertForbidden();
    }

    private function createConfirmedBooking(int $startsInHours): array
    {
        $instructor = User::factory()->create([
            'role' => UserRole::Instructor,
            'teaches_manual' => true,
            'teaches_automatic' => true,
        ]);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'automatic']);

        $slot = AvailabilitySlot::query()->create([
            'instructor_id' => $instructor->id,
            'start_time' => now()->addHours($startsInHours),
            'end_time' => now()->addHours($startsInHours + 1),
            'status' => 'approved',
        ]);

        $booking = $this->actingAs($student, 'sanctum')
            ->postJson('/api/bookings', ['slot_id' => $slot->id])
            ->json('data');

        $this->actingAs($supervisor, 'sanctum')
            ->postJson("/api/bookings/{$booking['id']}/assign", ['slot_id' => $slot->id]);

        return [Booking::query()->find($booking['id']), $instructor];
    }
}
