<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Ticket;
use App\Models\CRM\TicketMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    protected $slaService;

    public function __construct(\App\Services\CRM\SLAService $slaService)
    {
        $this->slaService = $slaService;
    }

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $tickets = Ticket::where('tenant_id', $tenantId)
            ->with(['contact', 'assignee', 'slaPolicy'])
            ->latest()
            ->paginate(15);

        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_id' => 'nullable|exists:crm_contacts,id',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket = Ticket::create([
            'tenant_id' => auth()->user()->tenant_id,
            'contact_id' => $request->contact_id,
            'created_by' => auth()->id(),
            'subject' => $request->subject,
            'description' => $request->description,
            'status' => 'open',
            'priority' => $request->priority,
        ]);

        $this->slaService->applySLA($ticket);

        return response()->json(['message' => 'Ticket created successfully', 'ticket' => $ticket->load('slaPolicy')], 201);
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeAccess($ticket);
        $ticket->load(['contact', 'assignee', 'messages.sender', 'creator', 'slaPolicy']);
        return response()->json($ticket);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeAccess($ticket);

        $request->validate([
            'status' => 'nullable|in:open,pending,resolved,closed',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $oldStatus = $ticket->status;
        $ticket->update($request->only(['status', 'priority', 'assigned_to', 'due_date']));

        if ($ticket->status === 'resolved' && $oldStatus !== 'resolved') {
            $this->slaService->recordResolution($ticket);
        }

        return response()->json(['message' => 'Ticket updated', 'ticket' => $ticket]);
    }

    public function addMessage(Request $request, Ticket $ticket)
    {
        $this->authorizeAccess($ticket);

        $request->validate([
            'message' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'sender_type' => 'agent',
            'message' => $request->message,
            'is_internal' => $request->is_internal ?? false,
        ]);

        if (!$message->is_internal) {
            $this->slaService->recordFirstResponse($ticket);
        }

        return response()->json(['message' => 'Response added', 'ticket_message' => $message], 201);
    }

    public function slaStats()
    {
        $tenantId = auth()->user()->tenant_id;
        $total = Ticket::where('tenant_id', $tenantId)->count();
        
        if ($total === 0) {
            return response()->json([
                'compliance' => 100,
                'avg_response' => '0m',
                'avg_resolution' => '0h',
                'breaches' => 0,
                'levels' => []
            ]);
        }

        $compliant = Ticket::where('tenant_id', $tenantId)->where('sla_status', 'compliant')->count();
        $breaches = Ticket::where('tenant_id', $tenantId)->whereIn('sla_status', ['response_breached', 'resolution_breached'])->count();
        
        // Avg Response (minutes)
        $avgResponse = Ticket::where('tenant_id', $tenantId)
            ->whereNotNull('first_response_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, first_response_at)) as avg')
            ->value('avg') ?? 0;

        // Avg Resolution (hours)
        $avgResolution = Ticket::where('tenant_id', $tenantId)
            ->where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg')
            ->value('avg') ?? 0;

        $levels = Ticket::where('tenant_id', $tenantId)
            ->selectRaw('priority, count(*) as total, sum(case when sla_status = "compliant" then 1 else 0 end) as compliant_count')
            ->groupBy('priority')
            ->get()
            ->map(function($row) {
                return [
                    'name' => ucfirst($row->priority),
                    'percent' => $row->total > 0 ? round(($row->compliant_count / $row->total) * 100) : 100
                ];
            });

        return response()->json([
            'compliance' => round(($compliant / $total) * 100, 1),
            'avg_response' => round($avgResponse) . 'm',
            'avg_resolution' => round($avgResolution, 1) . 'h',
            'breaches' => $breaches,
            'levels' => $levels
        ]);
    }

    protected function authorizeAccess(Ticket $ticket)
    {
        if ($ticket->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }
}
