<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdentityCard;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Employee;
use Inertia\Inertia;
use Illuminate\Support\Str;

class IdentityCardController extends Controller
{
    /**
     * The ID Card Studio Dashboard (Lifecycle Flow)
     */
    public function index()
    {
        // 1. Production Queue: Employees needing cards or in intermediary states
        // Including "Pending Photo" which will be flagged as "Action Required"
        $queue = Employee::with(['user.identityCard', 'department'])
            ->where(function($q) {
                $q->doesntHave('user.identityCard')
                  ->orWhereHas('user.identityCard', fn($sq) => $sq->whereIn('status', ['Draft', 'Pending Photo', 'In Production']));
            })
            ->latest()
            ->get()
            ->map(function ($e) {
                $card = $e->user->identityCard ?? null;
                $status = $card ? $card->status : ($e->avatar ? 'Draft' : 'Pending Photo');
                
                return [
                    'id' => $e->user_id,
                    'employee_id' => $e->id,
                    'employee_code' => $e->employee_code,
                    'name' => "{$e->first_name} {$e->last_name}",
                    'type' => 'Employee',
                    'department' => $e->department->name ?? 'General',
                    'joining_date' => $e->joining_date,
                    'avatar' => $e->avatar,
                    'status' => $status,
                    'action_required' => !$e->avatar && in_array($status, ['Draft', 'Pending Photo', 'In Production']),
                    'card_id' => $card ? $card->id : null
                ];
            });

        // 2. Expiring Vendor Contracts
        $expiringVendors = Vendor::where('contract_end_date', '<', now()->addMonth())
            ->where('is_active', true)
            ->get();

        // 3. The Registry (Active/Ready cards)
        $registry = IdentityCard::with(['user.employee', 'template'])->where('status', 'Active')->latest()->paginate(20);

        return Inertia::render('Admin/Identity/Index', [
            'queue' => [
                'items' => $queue,
                'expiring_vendors' => $expiringVendors
            ],
            'registry' => $registry,
            'stats' => [
                'issued_today' => IdentityCard::whereDate('issue_date', today())->count(),
                'active' => IdentityCard::where('status', 'Active')->count(),
                'revoked' => IdentityCard::whereIn('status', ['Revoked', 'Expired'])->count(),
                'pending' => IdentityCard::whereIn('status', ['Draft', 'Pending Photo', 'In Production'])->count()
            ],
            'templates' => \App\Models\CardTemplate::where('is_active', true)
                ->orderBy('is_default', 'desc')
                ->get()
        ]);
    }

    /**
     * Move Card to next state in the lifecycle
     */
    public function updateStatus(Request $request, $id)
    {
        $card = IdentityCard::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:Draft,Pending Photo,In Production,Active'
        ]);

        $card->update(['status' => $validated['status']]);
        
        return back()->with('success', "Card state updated to {$validated['status']}");
    }

    /**
     * Generate/Issue a Card (Initial state: Draft or Pending Photo)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:Employee,Vendor,Visitor',
            'user_id' => 'nullable|exists:users,id', // For Employee/Vendor mapping
            'template_id' => 'nullable|exists:card_templates,id',
            'details' => 'required|array',
            'valid_until' => 'nullable|date'
        ]);

        $employee = null;
        if ($request->user_id) {
            $employee = Employee::where('user_id', $request->user_id)->first();
        }

        $initialStatus = ($employee && !$employee->avatar) ? 'Pending Photo' : 'Draft';

        // Find Default Template if none provided
        $templateId = $validated['template_id'] ?? null;
        if (!$templateId) {
            $default = \App\Models\CardTemplate::where('type', $validated['type'])->where('is_default', true)->first();
            $templateId = $default?->id;
        }

        $card = IdentityCard::create([
            'user_id' => $request->user_id,
            'template_id' => $templateId,
            'type' => $request->type,
            'card_number' => IdentityCard::generateCardNumber(),
            'qr_code' => \Illuminate\Support\Str::uuid(),
            'issue_date' => now(),
            'valid_until' => $request->valid_until ?? now()->addYears(1),
            'details' => $validated['details'],
            'status' => $initialStatus,
            'asset_version' => 1
        ]);

        return back()->with('success', "Card Initialized: {$card->card_number} (Status: {$initialStatus})");
    }

    /**
     * Batch Initialize Cards
     */
    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'personnel' => 'required|array', // user_ids
            'type' => 'required|string',
            'template_id' => 'nullable|exists:card_templates,id'
        ]);

        foreach ($validated['personnel'] as $userId) {
            $employee = Employee::where('user_id', $userId)->with('department')->first();
            if (!$employee) continue;

            // Skip if already has card
            if (IdentityCard::where('user_id', $userId)->exists()) continue;

            $initialStatus = !$employee->avatar ? 'Pending Photo' : 'Draft';
            
            // Template selection
            $templateId = $validated['template_id'];
            if (!$templateId) {
                $default = \App\Models\CardTemplate::where('type', $validated['type'])->where('is_default', true)->first();
                $templateId = $default?->id;
            }

            IdentityCard::create([
                'user_id' => $userId,
                'template_id' => $templateId,
                'type' => $validated['type'],
                'card_number' => IdentityCard::generateCardNumber(),
                'qr_code' => \Illuminate\Support\Str::uuid(),
                'issue_date' => now(),
                'valid_until' => now()->addYears(1),
                'details' => [
                    'name' => $employee->full_name,
                    'role' => $employee->department->name ?? 'Staff',
                    'employee_code' => $employee->employee_code
                ],
                'status' => $initialStatus,
                'asset_version' => 1
            ]);
        }

        return back()->with('success', count($validated['personnel']) . ' Cards initialized in production stream.');
    }

    /**
     * The "Kill Switch"
     */
    public function revoke($id)
    {
        $card = IdentityCard::findOrFail($id);
        $card->update(['status' => 'Revoked']);
        return back()->with('success', 'Card Revoked. Access Denied immediately.');
    }

    /**
     * Public Verification Page (Mobile Scan)
     */
    public function verify($uuid)
    {
        $card = IdentityCard::with(['user.employee'])->where('qr_code', $uuid)->first();

        if (!$card) {
            return Inertia::render('Public/Identity/Verify', [
                'status' => 'NotFound',
                'message' => 'Invalid QR Code'
            ]);
        }

        // Logic Checks
        $isExpired = $card->valid_until && now()->gt($card->valid_until);
        $isRevoked = $card->status === 'Revoked';

        $finalStatus = 'Granted';
        if ($isRevoked) $finalStatus = 'Revoked';
        elseif ($isExpired) $finalStatus = 'Expired';

        return Inertia::render('Public/Identity/Verify', [
            'status' => $finalStatus,
            'card' => $card,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
    
    /**
     * Batch Print (PDF Generation Stub)
     */
    /**
     * Batch Print (Client-Side Rendering)
     */
    public function batchPrint(Request $request)
    {
        $request->validate([
            'cards' => 'required|array', // IDs
            'template_id' => 'nullable|exists:card_templates,id'
        ]);

        $cards = IdentityCard::with(['user.employee', 'template'])->whereIn('id', $request->cards)->get();
        // If template_id is null, use the default for the first card's type, or any default, or any active
        $template = null;
        if ($request->template_id) {
            $template = \App\Models\CardTemplate::find($request->template_id);
        } else {
            $type = $cards->first()?->type ?? 'Employee';
            $template = \App\Models\CardTemplate::where('type', $type)->where('is_default', true)->first() 
                ?? \App\Models\CardTemplate::where('is_default', true)->first()
                ?? \App\Models\CardTemplate::where('is_active', true)->first();
        }

        return Inertia::render('Admin/Identity/Print', [
            'cards' => $cards,
            'template' => $template,
            'templates' => \App\Models\CardTemplate::select('id', 'name')->get()
        ]);
    }
}
