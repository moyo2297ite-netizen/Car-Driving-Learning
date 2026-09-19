<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;

class AdminDashboardService
{
    public function summary(): array
    {
        return [
            'students_count' => User::query()->where('role', UserRole::Student)->count(),
            'instructors_count' => User::query()->where('role', UserRole::Instructor)->count(),
            'supervisors_count' => User::query()->where('role', UserRole::Supervisor)->count(),
            'bookings_count' => Booking::query()->count(),
            'completed_bookings' => Booking::query()->where('status', BookingStatus::Completed)->count(),
            'total_revenue' => Payment::query()->where('status', PaymentStatus::Confirmed)->sum('amount'),
            'pending_payments' => Payment::query()->where('status', PaymentStatus::Pending)->count(),
        ];
    }
}
