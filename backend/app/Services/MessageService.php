<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;

class MessageService
{
    public function send(User $sender, User $receiver, string $body): Message
    {
        if (! $sender->canChatWith($receiver)) {
            throw new DomainException('التواصل مسموح فقط بين الطالب والمشرف/الأدمن.');
        }

        return Message::query()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'body' => $body,
        ]);
    }

    public function markAsRead(User $user, Message $message): Message
    {
        if ($message->receiver_id !== $user->id) {
            throw new DomainException('لا يمكنك تحديد هذه الرسالة كمقروءة.');
        }

        $message->update(['read_at' => now()]);

        return $message->fresh();
    }

    public function conversation(User $user, User $other): Collection
    {
        if (! $user->canChatWith($other)) {
            throw new DomainException('لا يمكن عرض هذه المحادثة.');
        }

        return Message::query()
            ->where(function ($q) use ($user, $other) {
                $q->where('sender_id', $user->id)->where('receiver_id', $other->id);
            })
            ->orWhere(function ($q) use ($user, $other) {
                $q->where('sender_id', $other->id)->where('receiver_id', $user->id);
            })
            ->orderBy('created_at')
            ->get();
    }
}
