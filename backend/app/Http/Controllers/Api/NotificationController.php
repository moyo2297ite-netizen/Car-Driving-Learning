<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $request->user()->notifications()->limit(30)->get()->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? null,
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ]),
        ]);
    }

    public function markRead(Request $request, string $notification)
    {
        $request->user()->unreadNotifications()->where('id', $notification)->get()->each->markAsRead();

        return response()->json(['message' => 'تم.']);
    }
}
