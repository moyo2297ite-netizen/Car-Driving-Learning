<?php

namespace App\Notifications;

use App\Enums\AvailabilitySlotStatus;
use App\Models\AvailabilitySlot;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlotReviewed extends Notification
{
    use Queueable;

    public function __construct(private readonly AvailabilitySlot $slot) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->slot->status === AvailabilitySlotStatus::Approved) {
            return (new MailMessage)
                ->subject('تم اعتماد وقت توفرك')
                ->line('تم اعتماد الوقت المقترح: '.$this->slot->start_time->format('Y-m-d H:i'));
        }

        return (new MailMessage)
            ->subject('تم رفض وقت توفرك')
            ->line('تم رفض الوقت المقترح: '.$this->slot->start_time->format('Y-m-d H:i'))
            ->line('السبب: '.$this->slot->rejection_reason);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'slot_reviewed',
            'slot_id' => $this->slot->id,
            'status' => $this->slot->status->value,
            'rejection_reason' => $this->slot->rejection_reason,
        ];
    }
}
