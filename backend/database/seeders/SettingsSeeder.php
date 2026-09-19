<?php

namespace Database\Seeders;

use App\Services\SettingService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(SettingService::class);

        $settings->set(SettingService::LESSON_PRICE, 15);
        $settings->set(SettingService::CANCELLATION_GRACE_HOURS, 24);
        $settings->set(SettingService::CANCELLATION_PENALTY_PERCENT, 50);
        $settings->set(SettingService::SHAM_CASH_ACCOUNT, '');
    }
}
