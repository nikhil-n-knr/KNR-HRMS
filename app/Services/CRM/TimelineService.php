<?php

namespace App\Services\CRM;

use App\Models\CRM\EmailThread;
use App\Models\CRM\Meeting;
use App\Models\CRM\Activity;
use Illuminate\Support\Collection;

class TimelineService
{
    /**
     * Get the 360° timeline for a CRM entity.
     */
    public function getTimeline($trackable): Collection
    {
        $events = collect();

        // 1. Fetch Emails (via threads)
        $threads = EmailThread::where('trackable_type', get_class($trackable))
            ->where('trackable_id', $trackable->id)
            ->with('messages')
            ->get();

        foreach ($threads as $thread) {
            foreach ($thread->messages as $message) {
                $events->push((object)[
                    'id' => 'email_' . $message->id,
                    'type' => 'email',
                    'title' => $thread->subject,
                    'content' => $message->body_text,
                    'direction' => $message->direction,
                    'status' => $message->status,
                    'timestamp' => $message->sent_at,
                    'meta' => [
                        'from' => $message->from_email,
                        'to' => $message->to_emails,
                    ],
                ]);
            }
        }

        // 2. Fetch Meetings
        $meetings = Meeting::where('trackable_type', get_class($trackable))
            ->where('trackable_id', $trackable->id)
            ->get();

        foreach ($meetings as $meeting) {
            $events->push((object)[
                'id' => 'meeting_' . $meeting->id,
                'type' => 'meeting',
                'title' => $meeting->title,
                'content' => $meeting->notes,
                'status' => $meeting->status,
                'timestamp' => $meeting->start_time,
                'meta' => [
                    'location' => $meeting->location,
                    'duration' => $meeting->duration_minutes,
                ],
            ]);
        }

        // 3. Fetch Generic Activities (Notes, Calls, etc.)
        $activities = \App\Models\CRM\Activity::where('trackable_type', get_class($trackable))
            ->where('trackable_id', $trackable->id)
            ->with('user')
            ->get();

        foreach ($activities as $activity) {
            $events->push((object)[
                'id' => 'activity_' . $activity->id,
                'type' => $activity->type ?: 'note',
                'title' => $activity->subject ?: 'Activity Log',
                'content' => $activity->content,
                'status' => 'completed',
                'timestamp' => $activity->created_at,
                'meta' => [
                    'user' => $activity->user?->name,
                ],
            ]);
        }

        // 4. Fetch Handover Logs
        $handovers = \App\Models\CRM\HandoverLog::where('entity_type', class_basename($trackable))
            ->whereJsonContains('entity_ids', $trackable->id)
            ->with(['fromUser', 'toUser'])
            ->get();

        foreach ($handovers as $log) {
            $events->push((object)[
                'id' => 'handover_' . $log->id,
                'type' => 'handover',
                'title' => 'Ownership Handover',
                'content' => "Account transferred from {$log->fromUser?->name} to {$log->toUser?->name}",
                'status' => 'success',
                'timestamp' => $log->created_at,
                'meta' => [
                    'reason' => $log->reason,
                ],
            ]);
        }

        // 5. Sort by timestamp descending
        return $events->sortByDesc('timestamp')->values();
    }
}
