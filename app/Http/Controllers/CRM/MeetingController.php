<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Meeting;
use App\Models\Employee;
use App\Services\CRM\MeetingDataService;
use App\Services\CRM\MeetingProviderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\CRM\MeetingInvitation;
use App\Models\CRM\EmailThread;
use App\Models\CRM\EmailMessage;

class MeetingController extends Controller
{
    private $dataService;
    private $providerService;

    public function __construct(MeetingDataService $dataService, MeetingProviderService $providerService)
    {
        $this->dataService = $dataService;
        $this->providerService = $providerService;
    }

    /**
     * Entry point for standard Resource Route
     */
    public function index(Request $request)
    {
        return $this->hub($request);
    }

    /**
     * Complete Meeting Hub UI
     */
    public function hub(Request $request)
    {
        $sync = $this->dataService->getSyncData();
        $view = $request->query('view', 'calendar');
        $user = auth()->user();
        $tenantId = $user->tenant_id ?? 1;

        $query = Meeting::where('tenant_id', $tenantId);

        if ($view === 'calendar') {
            // Personal View: Show only my meetings
            $query->where('employee_id', $user->employee_id);
        }

        $events = $query->get()->map(fn($m) => [
            'id' => $m->id,
            'title' => $m->title,
            'start' => $m->start_time,
            'end' => $m->end_time,
            'color' => $m->status === 'cancelled' ? '#ef4444' : ($m->provider ? '#4f46e5' : '#10b981'),
            'extendedProps' => [
                'status' => $m->status,
                'provider' => $m->provider,
                'location' => $m->location,
                'employee_id' => $m->employee_id
            ]
        ]);

        return Inertia::render('CRM/Sections/Meetings/Hub', [
            'view' => $view,
            'employees' => $sync['employees'],
            'audience' => $sync['audience'],
            'events' => $events,
            'stats' => [
                'upcoming' => Meeting::where('tenant_id', $tenantId)->where('start_time', '>', now())->count(),
                'noShows' => Meeting::where('tenant_id', $tenantId)->where('status', Meeting::STATUS_NOSHOW)->count(),
                'utilization' => '84%',
            ]
        ]);
    }

    /**
     * Store and Generate Live Links
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'client_id' => 'nullable|exists:crm_clients,id',
            'title' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'provider' => 'nullable|in:zoom,meet,teams',
            'location' => 'nullable|string',
            'timezone' => 'nullable|string',
            'attendees' => 'nullable|array',
            'notes' => 'nullable|string',
            'reminders_config' => 'nullable|array'
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $meetingLink = null;

        if ($request->filled('provider')) {
            try {
                $meetingLink = $this->providerService->createMeeting($employee, $data);
            } catch (\Exception $e) {
                return response()->json(['error' => "Meeting Link Error: " . $e->getMessage()], 422);
            }
        }

        $meeting = Meeting::create(array_merge($data, [
            'tenant_id' => auth()->user()->tenant_id ?? 1,
            'uuid' => Str::uuid(),
            'status' => Meeting::STATUS_PENDING,
            'created_by' => auth()->id(),
            'link' => $meetingLink,
        ]));

        if ($request->has('attendees')) {
            foreach ($request->attendees as $att) {
                $type = $att['type'] ?? 'External';
                $isManual = str_starts_with($att['id'] ?? '', 'manual_');

                $modelNamespace = null;
                if (!$isManual) {
                    $modelNamespace = $type === 'Employee' ? 'App\Models\Employee' : ($type === 'Lead' ? 'App\Models\CRM\Lead' : 'App\Models\CRM\Contact');
                }

                $attendee = $meeting->attendees()->create([
                    'attendee_id' => $isManual ? null : $att['id'],
                    'attendee_type' => $modelNamespace,
                    'name' => $att['name'] ?? null,
                    'email' => $att['email'] ?? null,
                    'status' => Meeting::STATUS_PENDING
                ]);

                if ($attendee->email) {
                    Mail::to($attendee->email)->queue(new MeetingInvitation($meeting, $meeting->link));
                    
                    // Log in CRM Sent Folder
                    $this->logInvitationInCrm($meeting, $attendee);
                }
            }
        }

        return response()->json($meeting->load('attendees'));
    }

    /**
     * Log meeting invitation in CRM communication history
     */
    private function logInvitationInCrm(Meeting $meeting, $attendee)
    {
        $thread = EmailThread::create([
            'tenant_id' => $meeting->tenant_id,
            'subject' => "Session Invitation: " . $meeting->title,
            'trackable_type' => 'App\Models\CRM\Meeting',
            'trackable_id' => $meeting->id,
        ]);

        EmailMessage::create([
            'thread_id' => $thread->id,
            'message_id' => 'meet_' . Str::random(10),
            'from_email' => auth()->user()->email,
            'to_emails' => [$attendee->email],
            'body_html' => "You have been invited to a session: " . $meeting->title . ". Link: " . $meeting->link,
            'direction' => 'outbound',
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Drag-and-drop support
     */
    public function reschedule(Request $request, Meeting $meeting)
    {
        $meeting->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'rescheduled',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reports API
     */
    public function analytics(Request $request)
    {
        return response()->json([
            'noShows' => Meeting::where('status', Meeting::STATUS_NOSHOW)->count(),
            'byProvider' => Meeting::selectRaw('provider, count(*) as count')->whereNotNull('provider')->groupBy('provider')->get(),
            'trends' => Meeting::selectRaw('date(start_time) as date, count(*) as count')->groupBy('date')->get()
        ]);
    }

    public function noShow(Request $request, Meeting $meeting)
    {
        $meeting->update(['status' => Meeting::STATUS_NOSHOW]);
        return response()->json(['success' => true]);
    }
}
