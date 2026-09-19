<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public const LESSON_PRICE = 'lesson_price';

    public const CANCELLATION_GRACE_HOURS = 'cancellation_grace_hours';

    public const CANCELLATION_PENALTY_PERCENT = 'cancellation_penalty_percent';

    public const SHAM_CASH_ACCOUNT = 'sham_cash_account';

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()->where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    public function getFloat(string $key, float $default = 0): float
    {
        return (float) $this->get($key, $default);
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    public function set(string $key, mixed $value): Setting
    {
        return Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value],
        );
    }

    public function lessonPrice(): float
    {
        return $this->getFloat(self::LESSON_PRICE, 0);
    }

    public function cancellationGraceHours(): int
    {
        return $this->getInt(self::CANCELLATION_GRACE_HOURS, 24);
    }

    public function cancellationPenaltyPercent(): int
    {
        return $this->getInt(self::CANCELLATION_PENALTY_PERCENT, 50);
    }

    public function calculateCancellationPenalty(float $lessonPrice): float
    {
        $percent = $this->cancellationPenaltyPercent();

        return round($lessonPrice * ($percent / 100), 2);
    }
}
