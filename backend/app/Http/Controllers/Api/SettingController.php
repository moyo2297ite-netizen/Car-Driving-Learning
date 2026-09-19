<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Services\SettingService;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function index()
    {
        return response()->json([
            'lesson_price' => $this->settings->lessonPrice(),
            'cancellation_grace_hours' => $this->settings->cancellationGraceHours(),
            'cancellation_penalty_percent' => $this->settings->cancellationPenaltyPercent(),
            'sham_cash_account' => $this->settings->get(SettingService::SHAM_CASH_ACCOUNT),
        ]);
    }

    public function update(UpdateSettingsRequest $request)
    {
        foreach ($request->validated() as $key => $value) {
            $this->settings->set($key, $value);
        }

        return $this->index();
    }
}
