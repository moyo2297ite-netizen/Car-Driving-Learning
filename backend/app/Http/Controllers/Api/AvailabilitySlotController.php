<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectAvailabilitySlotRequest;
use App\Http\Requests\StoreAvailabilitySlotRequest;
use App\Http\Resources\AvailabilitySlotResource;
use App\Models\AvailabilitySlot;
use App\Notifications\SlotReviewed;
use App\Services\AvailabilitySlotService;
use Illuminate\Http\Request;

class AvailabilitySlotController extends Controller
{
    public function __construct(private readonly AvailabilitySlotService $slots) {}

    public function store(StoreAvailabilitySlotRequest $request)
    {
        $slot = $this->slots->propose($request->user(), $request->validated());

        return new AvailabilitySlotResource($slot);
    }

    public function mine(Request $request)
    {
        $slots = $request->user()->availabilitySlots()->orderByDesc('start_time')->get();

        return AvailabilitySlotResource::collection($slots);
    }

    public function pending()
    {
        return AvailabilitySlotResource::collection($this->slots->pendingApproval());
    }

    public function available(Request $request)
    {
        $slots = $this->slots->listApprovedForTransmission($request->query('transmission_type'));

        return AvailabilitySlotResource::collection($slots);
    }

    public function approve(Request $request, AvailabilitySlot $slot)
    {
        $slot = $this->slots->approve($request->user(), $slot);

        $slot->instructor->notify(new SlotReviewed($slot));

        return new AvailabilitySlotResource($slot);
    }

    public function reject(RejectAvailabilitySlotRequest $request, AvailabilitySlot $slot)
    {
        $slot = $this->slots->reject($request->user(), $slot, $request->reason);

        $slot->instructor->notify(new SlotReviewed($slot));

        return new AvailabilitySlotResource($slot);
    }
}
