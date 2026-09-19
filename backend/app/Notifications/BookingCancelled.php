<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('تم إلغاء الحجز')
            ->line('تم إلغاء حجز درس القيادة.');

        if ($this->booking->penalty_amount) {
            $mail->line('تم تطبيق غرامة إلغاء بقيمة: '.$this->booking->penalty_amount);
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_cancelled',
            'booking_id' => $this->booking->id,
            'penalty_amount' => $this->booking->penalty_amount,
        ];
    }
}
