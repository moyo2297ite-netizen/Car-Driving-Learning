<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Exceptions\DomainException;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Collection;

class BookingService
{
    public function __construct(
        private readonly SettingService $settings,
    ) {}

    public function requestBooking(User $student, AvailabilitySlot $slot): Booking
    {
        $this->ensureStudent($student);

        if ($student->transmission_type === null) {
            throw new DomainException('يجب تحديد نوع الغيار عند التسجيل.');
        }

        if (! $slot->isBookable()) {
            throw new DomainException('هذا الوقت غير متاح للحجز.');
        }

        $instructor = $slot->instructor;

        if (! $instructor->supportsTransmission($student->transmission_type)) {
            throw new DomainException('المدرب لا يدعم نوع الغيار المطلوب.');
        }

        $hasActiveBooking = Booking::query()
            ->where('slot_id', $slot->id)
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
            ->exists();

        if ($hasActiveBooking) {
            throw new DomainException('هذا الوقت محجوز مسبقاً.');
        }

        return Booking::query()->create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'price' => $this->settings->lessonPrice(),
            'status' => BookingStatus::Pending,
        ]);
    }

    public function assignInstructor(User $supervisor, Booking $booking, AvailabilitySlot $slot): Booking
    {
        $this->ensureSupervisorOrAdmin($supervisor);

        if ($booking->status !== BookingStatus::Pending) {
            throw new DomainException('يمكن تعيين المدرب للحجوزات المعلقة فقط.');
        }

        if (! $slot->isBookable()) {
            throw new DomainException('يجب اختيار وقت معتمد للحجز.');
        }

        $student = $booking->student;
        $instructor = $slot->instructor;

        if (! $student->transmission_type) {
            throw new DomainException('الطالب لم يحدد نوع الغيار.');
        }

        if (! $instructor->supportsTransmission($student->transmission_type)) {
            throw new DomainException('يجب اختيار مدرب يدعم نفس نوع غيار الطالب.');
        }

        $booking->update([
            'slot_id' => $slot->id,
            'assigned_by' => $supervisor->id,
            'status' => BookingStatus::Confirmed,
        ]);

        return $booking->fresh(['student', 'slot.instructor']);
    }

    public function complete(User $actor, Booking $booking): Booking
    {
        if (! $actor->isInstructor() && ! $actor->isSupervisor() && ! $actor->isAdmin()) {
            throw new DomainException('غير مصرح بإتمام هذا الحجز.');
        }

        if ($actor->isInstructor() && $booking->slot->instructor_id !== $actor->id) {
            throw new DomainException('لا يمكنك إتمام حجز ليس ضمن جدولك.');
        }

        if ($booking->status !== BookingStatus::Confirmed) {
            throw new DomainException('يمكن إتمام الحجوزات المؤكدة فقط.');
        }

        $booking->update(['status' => BookingStatus::Completed]);

        return $booking->fresh();
    }

    public function cancel(User $actor, Booking $booking): Booking
    {
        if ($booking->status === BookingStatus::Cancelled) {
            throw new DomainException('الحجز ملغى مسبقاً.');
        }

        if ($booking->status === BookingStatus::Completed) {
            throw new DomainException('لا يمكن إلغاء درس مكتمل.');
        }

        if ($actor->isInstructor()) {
            throw new DomainException('المدرب لا يلغي مباشرة — يطلب من المشرف.');
        }

        if ($actor->isStudent() && $booking->student_id !== $actor->id) {
            throw new DomainException('لا يمكنك إلغاء حجز طالب آخر.');
        }

        if (! $actor->isStudent() && ! $actor->isSupervisor() && ! $actor->isAdmin()) {
            throw new DomainException('غير مصرح بإلغاء هذا الحجز.');
        }

        $penalty = $this->calculatePenaltyIfLate($booking);

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_by' => $actor->id,
            'penalty_amount' => $penalty,
        ]);

        return $booking->fresh();
    }

    public function calculatePenaltyIfLate(Booking $booking): ?float
    {
        $slot = $booking->slot;
        $graceHours = $this->settings->cancellationGraceHours();
        $deadline = $slot->start_time->copy()->subHours($graceHours);

        if (now()->greaterThan($deadline)) {
            return $this->settings->calculateCancellationPenalty((float) $booking->price);
        }

        return null;
    }

    public function studentsForInstructor(User $instructor): Collection
    {
        $this->ensureInstructor($instructor);

        $studentIds = Booking::query()
            ->whereHas('slot', fn ($q) => $q->where('instructor_id', $instructor->id))
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
            ->pluck('student_id')
            ->unique();

        return User::query()->whereIn('id', $studentIds)->get();
    }

    public function todayForInstructor(User $instructor): Collection
    {
        $this->ensureInstructor($instructor);

        return Booking::query()
            ->with(['student', 'slot'])
            ->whereHas('slot', fn ($q) => $q->where('instructor_id', $instructor->id))
            ->whereHas('slot', fn ($q) => $q->whereDate('start_time', today()))
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
            ->get();
    }

    public function supervisorDailyBoard(): array
    {
        return [
            'today_bookings' => Booking::query()
                ->with(['student', 'slot.instructor'])
                ->whereHas('slot', fn ($q) => $q->whereDate('start_time', today()))
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
                ->get(),
            'upcoming_bookings' => Booking::query()
                ->with(['student', 'slot.instructor'])
                ->whereIn('bookings.status', [BookingStatus::Pending, BookingStatus::Confirmed])
                ->whereHas('slot', fn ($q) => $q->where('start_time', '>', now()))
                ->join('availability_slots', 'bookings.slot_id', '=', 'availability_slots.id')
                ->orderBy('availability_slots.start_time')
                ->select('bookings.*')
                ->get(),
        ];
    }

    private function ensureStudent(User $user): void
    {
        if ($user->role !== UserRole::Student) {
            throw new DomainException('هذه العملية متاحة للطلاب فقط.');
        }
    }

    private function ensureInstructor(User $user): void
    {
        if ($user->role !== UserRole::Instructor) {
            throw new DomainException('هذه العملية متاحة للمدربين فقط.');
        }
    }

    private function ensureSupervisorOrAdmin(User $user): void
    {
        if (! $user->isAdmin() && ! $user->isSupervisor()) {
            throw new DomainException('هذه العملية متاحة للمشرف أو الأدمن فقط.');
        }
    }
}
