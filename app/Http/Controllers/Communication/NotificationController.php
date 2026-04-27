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
                $payload = is_array($n->data) ? $n->data : [];

                return [
                    'id' => $n->id,
                    'type' => $payload['type'] ?? 'alert',
                    'title' => $this->formatTitle($n),
                    'message' => $this->formatMessage($payload),
                    'created_at' => $n->created_at->diffForHumans(),
                    'created_at_full' => $n->created_at->format('d M Y h:i:s A'),
                    'read_at' => $n->read_at,
                    'data' => $payload,
                    'details' => $this->formatDetails($payload, $n->created_at->format('d M Y h:i:s A')),
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
            case 'plan_overwritten': return 'Locked Plan Modified';
            case 'bug_stage_changed': return 'Bug Stage Updated';
            case 'bug_assigned': return 'Bug Assigned';
            case 'task_assigned': return 'New Assignment';
            case 'sprint_status': return 'Sprint Update';
            case 'interview_scheduled': return 'Interview Scheduled';
            case 'interview_cancelled': return 'Interview Cancelled';
            default: return 'Notification';
        }
    }

    private function formatMessage(array $data): string
    {
        if (!empty($data['message'])) {
            return (string) $data['message'];
        }

        $type = (string) ($data['type'] ?? '');
        $projectName = $data['project_name'] ?? (!empty($data['project_id']) ? ('Project #' . $data['project_id']) : null);

        switch ($type) {
            case 'task_moved':
                $task = $data['task_title'] ?? (!empty($data['task_id']) ? ('Task #' . $data['task_id']) : 'Task');
                $from = $data['old_stage'] ?? 'Unknown';
                $to = $data['new_stage'] ?? 'Unknown';
                $by = $data['moved_by'] ?? 'System';
                return "{$task} moved from {$from} to {$to} by {$by}" . ($projectName ? " in {$projectName}." : '.');

            case 'plan_overwritten':
                $target = $data['task_title'] ?? $projectName ?? 'Locked plan';
                $by = $data['overwritten_by'] ?? 'System';
                $changeCount = is_array($data['changes'] ?? null) ? count($data['changes']) : 0;
                $suffix = $changeCount > 0 ? " ({$changeCount} field" . ($changeCount > 1 ? 's' : '') . ' changed).' : '.';
                return "{$target} was modified by {$by}{$suffix}";

            case 'bug_stage_changed':
                $bug = !empty($data['bug_id']) ? ('Bug #' . $data['bug_id']) : 'Bug';
                $subject = !empty($data['subject']) ? (' - ' . $data['subject']) : '';
                $from = $data['old_stage'] ?? 'Unknown';
                $to = $data['new_stage'] ?? 'Unknown';
                return "{$bug}{$subject} moved from {$from} to {$to}" . ($projectName ? " in {$projectName}." : '.');

            case 'bug_assigned':
                $bug = !empty($data['bug_id']) ? ('Bug #' . $data['bug_id']) : 'Bug';
                $subject = !empty($data['subject']) ? (' - ' . $data['subject']) : '';
                return "{$bug}{$subject} has been assigned" . ($projectName ? " in {$projectName}." : '.');
        }

        return 'You have a new update.';
    }

    private function formatDetails(array $data, ?string $createdAtFull = null): array
    {
        $details = [];

        if (!empty($createdAtFull)) {
            $details[] = ['label' => 'Date & Time', 'value' => $createdAtFull];
        }

        if (!empty($data['project_name'])) {
            $details[] = ['label' => 'Project', 'value' => (string) $data['project_name']];
        } elseif (!empty($data['project_id'])) {
            $details[] = ['label' => 'Project', 'value' => 'Project #' . $data['project_id']];
        }

        if (!empty($data['task_title'])) {
            $details[] = ['label' => 'Task', 'value' => (string) $data['task_title']];
        }

        if (!empty($data['subject']) && !empty($data['bug_id'])) {
            $details[] = ['label' => 'Bug', 'value' => '#' . $data['bug_id'] . ' - ' . $data['subject']];
        }

        if (!empty($data['old_stage']) || !empty($data['new_stage'])) {
            $details[] = [
                'label' => 'Stage',
                'value' => ($data['old_stage'] ?? 'Unknown') . ' -> ' . ($data['new_stage'] ?? 'Unknown'),
            ];
        }

        if (!empty($data['moved_by'])) {
            $details[] = ['label' => 'Updated By', 'value' => (string) $data['moved_by']];
        }

        if (!empty($data['overwritten_by'])) {
            $details[] = ['label' => 'Updated By', 'value' => (string) $data['overwritten_by']];
        }

        if (is_array($data['changes'] ?? null) && !empty($data['changes'])) {
            foreach ($data['changes'] as $field => $change) {
                $old = is_array($change) ? ($change['old'] ?? null) : null;
                $new = is_array($change) ? ($change['new'] ?? null) : $change;
                $oldValue = $this->formatChangeValue($old, true);
                $newValue = $this->formatChangeValue($new, false);
                $details[] = [
                    'label' => 'Changed: ' . str_replace('_', ' ', (string) $field),
                    'value' => (string) $oldValue . ' -> ' . (string) $newValue,
                ];
            }
        }

        return $details;
    }

    private function formatChangeValue($value, bool $isOld): string
    {
        if ($value === null || $value === '') {
            return $isOld ? '(current)' : '-';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return (string) json_encode($value);
    }
}
