<?php

namespace App\Services\Calendar\Sources;

use App\Services\Calendar\CalendarEvent;
use App\Services\Calendar\CalendarEventSource;
use App\Models\Interview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InterviewEventSource implements CalendarEventSource
{
    public function getEvents(Carbon $start, Carbon $end, User $user): Collection
    {
        // Logic: Show interviews where I am the interviewer
        // OR if I am an admin/recruiter, maybe show all?
        // For now, let's stick to "My Workload" + "All" if requested (handled by service level or explicit toggle?)
        // Let's implement strict "Assigned to Me" for "My Workload" context.
        
        $query = Interview::query()
            ->with(['application.candidate', 'application.job'])
            ->whereBetween('scheduled_at', [$start, $end]);

        if (!$user->can('view_all_interviews')) {
            $query->where('interviewer_id', $user->id);
        }

        $interviews = $query->get();

        return $interviews->map(function (Interview $interview) {
            $candidateName = $interview->application->candidate->name ?? 'Unknown Candidate';
            $jobTitle = $interview->application->job->title ?? 'Unknown Job';
            
            return new CalendarEvent(
                id: 'interview-' . $interview->id,
                title: "Interview: $candidateName ($jobTitle)",
                start: Carbon::parse($interview->scheduled_at),
                end: Carbon::parse($interview->scheduled_at)->addMinutes($interview->duration ?? 60),
                type: 'interview',
                color: 'indigo', // Indigo for interviews
                allDay: false,
                metadata: [
                    'candidate_id' => $interview->application->candidate_id,
                    'interview_id' => $interview->id,
                    'status' => $interview->status,
                    'round' => $interview->round,
                    'link' => route('talent.candidates.index', ['open_id' => $interview->application->candidate_id])
                ]
            );
        });
    }
}
