<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\UserResource;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingConfirmed;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function store(StoreBookingRequest $request)
    {
        $slot = AvailabilitySlot::query()->findOrFail($request->slot_id);

        $booking = $this->bookings->requestBooking($request->user(), $slot);

        return new BookingResource($booking->load(['student', 'slot.instructor']));
    }

    public function mine(Request $request)
    {
        $bookings = $request->user()->studentBookings()
            ->with(['slot.instructor', 'payments'])
            ->latest()
            ->get();

        return BookingResource::collection($bookings);
    }

    public function assign(AssignBookingRequest $request, Booking $booking)
    {
        $slot = AvailabilitySlot::query()->findOrFail($request->slot_id);

        $booking = $this->bookings->assignInstructor($request->user(), $booking, $slot);

        $booking->student->notify(new BookingConfirmed($booking));

        return new BookingResource($booking);
    }

    public function complete(Request $request, Booking $booking)
    {
        $booking = $this->bookings->complete($request->user(), $booking);

        return new BookingResource($booking);
    }

    public function cancel(Request $request, Booking $booking)
    {
        $booking = $this->bookings->cancel($request->user(), $booking);

        $booking->student->notify(new BookingCancelled($booking));

        return new BookingResource($booking);
    }

    public function pending()
    {
        $board = $this->bookings->supervisorDailyBoard();

        return response()->json([
            'today_bookings' => BookingResource::collection($board['today_bookings']),
            'upcoming_bookings' => BookingResource::collection($board['upcoming_bookings']),
        ]);
    }

    public function today(Request $request)
    {
        return BookingResource::collection($this->bookings->todayForInstructor($request->user()));
    }

    public function students(Request $request)
    {
        return UserResource::collection($this->bookings->studentsForInstructor($request->user()));
    }
}
