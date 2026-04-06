<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        // 1. Fetch user notifications (standard Laravel trait)
        $user = Auth::user();
        
        // Paginate - 20 per page
        $notifications = $user->notifications()->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
