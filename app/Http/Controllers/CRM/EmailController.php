<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\EmailAccount;
use App\Models\CRM\EmailThread;
use App\Models\CRM\EmailMessage;
use App\Services\CRM\EmailSyncService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailController extends Controller
{
    protected $syncService;

    public function __construct(EmailSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Display the email hub (Inbox).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $account = EmailAccount::where('user_id', $user->id)->first();

        if (!$account) {
            return Inertia::render('CRM/Sections/EmailHub/Setup');
        }

        $threads = EmailThread::where('tenant_id', $user->tenant_id)
            ->with(['messages' => function ($q) {
                $q->latest()->limit(1);
            }, 'trackable'])
            ->latest()
            ->paginate(20);

        return Inertia::render('CRM/Sections/EmailHub/Index', [
            'threads' => $threads,
            'account' => $account,
        ]);
    }

    /**
     * Display a specific email thread.
     */
    public function show(EmailThread $thread)
    {
        $thread->load(['messages.attachments', 'trackable']);
        
        return Inertia::render('CRM/Sections/EmailHub/Thread', [
            'thread' => $thread,
        ]);
    }

    /**
     * Send a new email.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|array',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'trackable_type' => 'nullable|string',
            'trackable_id' => 'nullable|integer',
        ]);

        // 1. Send via Service (SMTP logic)
        // placeholder for mailer logic

        // 2. Log in CRM
        $thread = EmailThread::create([
            'tenant_id' => auth()->user()->tenant_id,
            'subject' => $request->subject,
            'trackable_type' => $request->trackable_type,
            'trackable_id' => $request->trackable_id,
        ]);

        $message = EmailMessage::create([
            'thread_id' => $thread->id,
            'message_id' => uniqid('msg_'),
            'from_email' => auth()->user()->email,
            'to_emails' => $request->to,
            'body_html' => $request->body,
            'direction' => 'outbound',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Email sent successfully.');
    }

    /**
     * Store a manually logged (offline) email.
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|array',
            'from' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'direction' => 'required|in:inbound,outbound',
            'sent_at' => 'required|date',
            'source' => 'required|string', // e.g., 'phone', 'direct', 'offline'
            'trackable_type' => 'nullable|string',
            'trackable_id' => 'nullable|integer',
        ]);

        $thread = EmailThread::create([
            'tenant_id' => auth()->user()->tenant_id,
            'subject' => $request->subject,
            'trackable_type' => $request->trackable_type,
            'trackable_id' => $request->trackable_id,
        ]);

        $message = EmailMessage::create([
            'thread_id' => $thread->id,
            'message_id' => uniqid('msg_manual_'),
            'from_email' => $request->from,
            'to_emails' => $request->to,
            'body_html' => $request->body,
            'direction' => $request->direction,
            'status' => 'logged',
            'is_manual' => true,
            'source' => $request->source,
            'sent_at' => $request->sent_at,
        ]);

        return redirect()->back()->with('success', 'Offline email logged successfully.');
    }

    /**
     * Sync the current user's email account.
     */
    public function sync()
    {
        $account = EmailAccount::where('user_id', auth()->id())->firstOrFail();
        $this->syncService->syncAccount($account);

        return redirect()->back()->with('success', 'Syncing in progress...');
    }
}
