<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\Communication\NotificationService;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    protected $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Fetch Notifications with Pagination
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->data['type'] ?? 'alert',
                    'title' => $this->formatTitle($n),
                    'message' => $n->data['message'] ?? '',
                    'created_at' => $n->created_at->diffForHumans(),
                    'read_at' => $n->read_at,
                    'data' => $n->data
                ];
            });

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Handle User Action (Click, Acknowledge, Dismiss)
     * Returns: JSON with redirect_url (if click) or status.
     */
    public function handleAction(Request $request, $id, $action)
    {
        $notification = DatabaseNotification::findOrFail($id);
        
        // 1. Log Interaction & Mark Read
        $this->service->logInteraction($request->user(), $id, $action, $request);

        // 2. Handle specific action returns
        if ($action === 'clicked') {
            return response()->json([
                'success' => true,
                'redirect_url' => $this->service->getRedirectUrl($notification)
            ]);
        }
        
        return response()->json(['success' => true]);
    }
    
    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }

    // Helper to format title (could be moved to Vue or Service)
    private function formatTitle($n)
    {
        $type = $n->data['type'] ?? '';
        switch ($type) {
            case 'task_moved': return 'Task Update';
            case 'task_assigned': return 'New Assignment';
            case 'sprint_status': return 'Sprint Update';
            case 'interview_scheduled': return 'Interview Scheduled';
            case 'interview_cancelled': return 'Interview Cancelled';
            default: return 'Notification';
        }
    }
}
