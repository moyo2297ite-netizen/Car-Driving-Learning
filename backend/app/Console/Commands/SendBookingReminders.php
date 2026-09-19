<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Notifications\BookingReminder;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';

    protected $description = 'يرسل تذكيراً للطلاب بحجوزاتهم المؤكدة خلال الـ 24 ساعة القادمة';

    public function handle(): int
    {
        $bookings = Booking::query()
            ->with(['student', 'slot.instructor'])
            ->where('status', BookingStatus::Confirmed)
            ->whereHas('slot', function ($q) {
                $q->whereBetween('start_time', [now()->addHours(23), now()->addHours(24)]);
            })
            ->get();

        foreach ($bookings as $booking) {
            $booking->student->notify(new BookingReminder($booking));
        }

        $this->info("تم إرسال {$bookings->count()} تذكير.");

        return self::SUCCESS;
    }
}
