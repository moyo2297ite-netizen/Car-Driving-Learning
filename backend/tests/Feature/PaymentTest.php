<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function makeConfirmedBooking(): array
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
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'status' => 'approved',
        ]);

        $booking = Booking::query()->create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'price' => 20,
            'status' => 'confirmed',
            'assigned_by' => $supervisor->id,
        ]);

        return compact('instructor', 'supervisor', 'student', 'booking');
    }

    public function test_student_can_record_cash_payment_for_own_booking(): void
    {
        ['student' => $student, 'booking' => $booking] = $this->makeConfirmedBooking();

        $this->actingAs($student, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/payments", [
                'method' => 'cash',
                'amount' => 20,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.method', 'cash');
    }

    public function test_student_cannot_record_payment_for_another_students_booking(): void
    {
        ['booking' => $booking] = $this->makeConfirmedBooking();
        $otherStudent = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'automatic']);

        $this->actingAs($otherStudent, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/payments", [
                'method' => 'cash',
                'amount' => 20,
            ])
            ->assertUnprocessable();
    }

    public function test_supervisor_can_confirm_payment_and_it_appears_in_pending_before_confirmation(): void
    {
        ['student' => $student, 'supervisor' => $supervisor, 'booking' => $booking] = $this->makeConfirmedBooking();

        $payment = $this->actingAs($student, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/payments", [
                'method' => 'sham_cash',
                'amount' => 20,
            ])->json('data');

        $pending = $this->actingAs($supervisor, 'sanctum')
            ->getJson('/api/payments/pending')
            ->assertOk();

        $this->assertCount(1, $pending->json('data'));

        $this->actingAs($supervisor, 'sanctum')
            ->postJson("/api/payments/{$payment['id']}/confirm")
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $this->actingAs($supervisor, 'sanctum')
            ->getJson('/api/payments/pending')
            ->assertJsonCount(0, 'data');
    }

    public function test_instructor_cannot_confirm_payments(): void
    {
        ['instructor' => $instructor, 'student' => $student, 'booking' => $booking] = $this->makeConfirmedBooking();

        $payment = $this->actingAs($student, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/payments", [
                'method' => 'cash',
                'amount' => 20,
            ])->json('data');

        $this->actingAs($instructor, 'sanctum')
            ->postJson("/api/payments/{$payment['id']}/confirm")
            ->assertForbidden();
    }

    public function test_sham_cash_link_reflects_configured_account(): void
    {
        ['student' => $student] = $this->makeConfirmedBooking();

        app(SettingService::class)->set(SettingService::SHAM_CASH_ACCOUNT, 'school-account');

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/payments/sham-cash-link')
            ->assertOk()
            ->assertJsonPath('link', 'shamcash://pay?account=school-account');
    }
}
