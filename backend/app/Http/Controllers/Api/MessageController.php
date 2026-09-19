<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private readonly MessageService $messages) {}

    public function store(StoreMessageRequest $request)
    {
        $receiver = User::query()->findOrFail($request->receiver_id);

        $message = $this->messages->send($request->user(), $receiver, $request->body);

        return new MessageResource($message->load(['sender', 'receiver']));
    }

    public function conversation(Request $request, User $user)
    {
        $messages = $this->messages->conversation($request->user(), $user);

        return MessageResource::collection($messages->load(['sender', 'receiver']));
    }

    public function markRead(Request $request, Message $message)
    {
        $message = $this->messages->markAsRead($request->user(), $message);

        return new MessageResource($message);
    }

    public function threads(Request $request)
    {
        $user = $request->user();

        $studentIds = Message::query()
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->get(['sender_id', 'receiver_id'])
            ->flatMap(fn ($m) => [$m->sender_id, $m->receiver_id])
            ->unique()
            ->reject(fn ($id) => $id === $user->id);

        $students = User::query()
            ->whereIn('id', $studentIds)
            ->where('role', UserRole::Student)
            ->get();

        return UserResource::collection($students);
    }
}
