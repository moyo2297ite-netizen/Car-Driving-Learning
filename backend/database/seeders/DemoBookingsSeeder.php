<?php

namespace Database\Seeders;

use App\Enums\AvailabilitySlotStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;

// بيانات تجريبية قليلة بس واقعية، عشان الصفحات ما تظهر فاضية وقت التجربة.
// كل شي هون idempotent (firstOrCreate) فتقدر تشغّل الـ seeder أكتر من مرة
// بدون ما يكرر الصفوف.
class DemoBookingsSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::query()->where('role', UserRole::Instructor)->first();
        $supervisor = User::query()->where('role', UserRole::Supervisor)->first();
        $student = User::query()->where('role', UserRole::Student)->first();

        if (! $instructor || ! $supervisor || ! $student) {
            return;
        }

        $approvedSlot = AvailabilitySlot::query()->firstOrCreate(
            ['instructor_id' => $instructor->id, 'start_time' => now()->addDays(2)->setTime(9, 0)],
            ['end_time' => now()->addDays(2)->setTime(10, 0), 'status' => AvailabilitySlotStatus::Approved],
        );

        AvailabilitySlot::query()->firstOrCreate(
            ['instructor_id' => $instructor->id, 'start_time' => now()->addDays(4)->setTime(14, 0)],
            ['end_time' => now()->addDays(4)->setTime(16, 0), 'status' => AvailabilitySlotStatus::Proposed],
        );

        $confirmedBooking = Booking::query()->firstOrCreate(
            ['student_id' => $student->id, 'slot_id' => $approvedSlot->id],
            [
                'assigned_by' => $supervisor->id,
                'price' => 15,
                'status' => BookingStatus::Confirmed,
            ],
        );

        Payment::query()->firstOrCreate(
            ['booking_id' => $confirmedBooking->id],
            ['method' => PaymentMethod::ShamCash, 'amount' => 15, 'status' => PaymentStatus::Pending],
        );

        // أول ٣ مهارات منجزة، الباقي لأ — عشان شريط التقدّم يبيّن شكل واقعي
        Skill::query()->orderBy('id')->get()->each(function ($skill, $index) use ($student, $instructor) {
            $student->studentSkills()->firstOrCreate(
                ['skill_id' => $skill->id],
                [
                    'is_completed' => $index < 3,
                    'updated_by' => $instructor->id,
                    'skill_updated_at' => $index < 3 ? now()->subDays(1) : null,
                ],
            );
        });
    }
}
