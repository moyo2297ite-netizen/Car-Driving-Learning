<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AvailabilitySlotResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PaymentResource;
use App\Services\AdminDashboardService;
use App\Services\AvailabilitySlotService;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $adminDashboard,
        private readonly BookingService $bookings,
        private readonly AvailabilitySlotService $slots,
        private readonly PaymentService $payments,
    ) {}

    public function admin()
    {
        return response()->json($this->adminDashboard->summary());
    }

    public function supervisor()
    {
        $board = $this->bookings->supervisorDailyBoard();

        return response()->json([
            'today_bookings' => BookingResource::collection($board['today_bookings']),
            'upcoming_bookings' => BookingResource::collection($board['upcoming_bookings']),
            'pending_slots' => AvailabilitySlotResource::collection($this->slots->pendingApproval()),
            'pending_payments' => PaymentResource::collection($this->payments->pendingConfirmation()),
        ]);
    }

    public function instructor(Request $request)
    {
        return response()->json([
            'today_bookings' => BookingResource::collection($this->bookings->todayForInstructor($request->user())),
            'my_slots' => AvailabilitySlotResource::collection(
                $request->user()->availabilitySlots()->orderByDesc('start_time')->get()
            ),
        ]);
    }
}
