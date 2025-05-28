<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'all' => $user->notifications,
            'unread' => $user->unreadNotifications
        ]);
    }

    // public function markAsRead(Request $request, $id)
    // {
    //     $notification = $request->user()->notifications()->findOrFail($id);
    //     $notification->markAsRead();

    //     return response()->json(['message' => 'Notification marquée comme lue.']);
    // }
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification non trouvée'], 404);
        }

        $notification->markAsRead(); // ✅ Met à jour la colonne `read_at`
        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues.']);
    }
}
