<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotificationLog;
use App\Services\Infrastructure\LoggerService;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Get Recent Notifications
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markRead(Request $request, $id)
    {
        $user = $request->user();
        
        if ($id === 'all') {
            $user->unreadNotifications->markAsRead();
            return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
        }

        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
    }
    
    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();
        return response()->json(['count' => $count]);
    }
}
