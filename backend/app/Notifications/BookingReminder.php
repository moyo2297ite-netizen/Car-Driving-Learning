<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminder extends Notification
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تذكير بموعد درسك')
            ->line('لديك درس قيادة غداً الساعة: '.$this->booking->slot->start_time->format('H:i'))
            ->line('المدرب: '.$this->booking->slot->instructor->name);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_reminder',
            'booking_id' => $this->booking->id,
            'start_time' => $this->booking->slot->start_time->toIso8601String(),
        ];
    }
}
