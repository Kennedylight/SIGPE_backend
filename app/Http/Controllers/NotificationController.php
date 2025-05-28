<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:etudiant-api,enseignant-api,admin-api');
    }

    private function getAuthenticatedUser()
    {
        // Cette méthode est maintenant plus simple car le middleware a déjà fait le travail
        return auth()->user();
    }

    public function index()
    {
        $user = $this->getAuthenticatedUser();
        
        return response()->json([
            'all' => $user->notifications,
            'unread' => $user->unreadNotifications
        ]);
    }

    public function markAsRead($id)
    {
        $user = $this->getAuthenticatedUser();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = $this->getAuthenticatedUser();
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues.']);
    }

    public function destroy($id)
    {
        $user = $this->getAuthenticatedUser();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        
        $notification->delete();
        
        return response()->json(['success' => true, 'message' => 'Notification supprimée avec succès']);
    }

    public function destroyAll()
    {
        $user = $this->getAuthenticatedUser();
        $user->notifications()->delete();
        
        return response()->json(['success' => true, 'message' => 'Toutes les notifications ont été supprimées']);
    }
}
