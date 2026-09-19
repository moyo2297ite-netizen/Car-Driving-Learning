<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\PaymentConfirmed;
use App\Services\PaymentService;
use App\Services\SettingService;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly SettingService $settings,
    ) {}

    public function store(StorePaymentRequest $request, Booking $booking)
    {
        $payment = $this->payments->record(
            $request->user(),
            $booking,
            $request->enum('method', PaymentMethod::class),
            (float) $request->amount,
        );

        return new PaymentResource($payment);
    }

    public function confirm(Payment $payment)
    {
        $payment = $this->payments->confirm(request()->user(), $payment);

        $payment->booking->student->notify(new PaymentConfirmed($payment));

        return new PaymentResource($payment);
    }

    public function pending()
    {
        return PaymentResource::collection($this->payments->pendingConfirmation());
    }

    public function shamCashLink()
    {
        return response()->json([
            'link' => $this->payments->shamCashLink($this->settings),
        ]);
    }
}
