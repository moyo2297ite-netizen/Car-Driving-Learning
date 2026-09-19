<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\DomainException;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Collection;

class PaymentService
{
    public function record(User $actor, Booking $booking, PaymentMethod $method, float $amount): Payment
    {
        if (! $actor->isStudent() && ! $actor->isSupervisor() && ! $actor->isAdmin()) {
            throw new DomainException('غير مصرح بتسجيل الدفع.');
        }

        if ($actor->isStudent() && $booking->student_id !== $actor->id) {
            throw new DomainException('لا يمكنك تسجيل دفع لحجز طالب آخر.');
        }

        return Payment::query()->create([
            'booking_id' => $booking->id,
            'method' => $method,
            'amount' => $amount,
            'status' => PaymentStatus::Pending,
        ]);
    }

    public function confirm(User $supervisor, Payment $payment): Payment
    {
        $this->ensureSupervisorOrAdmin($supervisor);

        if ($payment->status === PaymentStatus::Confirmed) {
            throw new DomainException('الدفعة مؤكدة مسبقاً.');
        }

        $payment->update([
            'status' => PaymentStatus::Confirmed,
            'confirmed_by' => $supervisor->id,
        ]);

        return $payment->fresh(['booking', 'confirmedBy']);
    }

    public function pendingConfirmation(): Collection
    {
        return Payment::query()
            ->with(['booking.student'])
            ->where('status', PaymentStatus::Pending)
            ->latest()
            ->get();
    }

    public function shamCashLink(SettingService $settings): ?string
    {
        $account = $settings->get(SettingService::SHAM_CASH_ACCOUNT);

        if (! $account) {
            return null;
        }

        return 'shamcash://pay?account='.urlencode((string) $account);
    }

    private function ensureSupervisorOrAdmin(User $user): void
    {
        if (! $user->isAdmin() && ! $user->isSupervisor()) {
            throw new DomainException('تأكيد الدفع متاح للمشرف أو الأدمن فقط.');
        }
    }
}
