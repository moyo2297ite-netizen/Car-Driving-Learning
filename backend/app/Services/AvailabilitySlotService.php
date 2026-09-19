<?php

namespace App\Services;

use App\Enums\AvailabilitySlotStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Exceptions\DomainException;
use App\Models\AvailabilitySlot;
use App\Models\User;
use Illuminate\Support\Collection;

class AvailabilitySlotService
{
    public function propose(User $instructor, array $data): AvailabilitySlot
    {
        $this->ensureInstructor($instructor);

        return AvailabilitySlot::query()->create([
            'instructor_id' => $instructor->id,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => AvailabilitySlotStatus::Proposed,
        ]);
    }

    public function approve(User $supervisor, AvailabilitySlot $slot): AvailabilitySlot
    {
        $this->ensureSupervisorOrAdmin($supervisor);

        if ($slot->status !== AvailabilitySlotStatus::Proposed) {
            throw new DomainException('يمكن اعتماد الأوقات المقترحة فقط.');
        }

        $slot->update([
            'status' => AvailabilitySlotStatus::Approved,
            'rejection_reason' => null,
        ]);

        return $slot->fresh();
    }

    public function reject(User $supervisor, AvailabilitySlot $slot, string $reason): AvailabilitySlot
    {
        $this->ensureSupervisorOrAdmin($supervisor);

        if ($slot->status !== AvailabilitySlotStatus::Proposed) {
            throw new DomainException('يمكن رفض الأوقات المقترحة فقط.');
        }

        $slot->update([
            'status' => AvailabilitySlotStatus::Rejected,
            'rejection_reason' => $reason,
        ]);

        return $slot->fresh();
    }

    public function listApprovedForTransmission(?string $transmissionType = null): Collection
    {
        $query = AvailabilitySlot::query()
            ->with('instructor')
            ->where('status', AvailabilitySlotStatus::Approved)
            ->where('start_time', '>', now())
            ->whereDoesntHave('bookings', function ($q) {
                $q->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed]);
            });

        if ($transmissionType !== null) {
            $query->whereHas('instructor', function ($q) use ($transmissionType) {
                if ($transmissionType === 'manual') {
                    $q->where('teaches_manual', true);
                } else {
                    $q->where('teaches_automatic', true);
                }
            });
        }

        return $query->orderBy('start_time')->get();
    }

    public function pendingApproval(): Collection
    {
        return AvailabilitySlot::query()
            ->with('instructor')
            ->where('status', AvailabilitySlotStatus::Proposed)
            ->orderBy('start_time')
            ->get();
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
