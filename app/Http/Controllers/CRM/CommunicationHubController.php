<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Contact;
use App\Models\CRM\EmailMessage;
use App\Models\CRM\EmailStat;
use App\Models\CRM\CampaignJourney;
use App\Models\CRM\EmailAccount;
use App\Models\CRM\EmailThread;
use App\Models\CRM\Meeting;
use App\Services\CRM\HubController as MainHubController;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommunicationHubController extends Controller
{
    /**
     * Display the Communication & Meetings Hub.
     */
    public function index(Request $request)
    {
        $section = $request->input('section', 'communications');
        $tab = $request->input('tab', 'inbox');
        $viewType = $request->input('view_type', 'personal'); // 'personal' or 'team'
        $user = auth()->user();
        $isAdmin = $user->roles->contains('name', 'Admin');

        $data = [
            'section' => $section,
            'tab' => $tab,
            'viewType' => $viewType,
            'isAdmin' => $isAdmin,
            'tabs' => $this->getTabs(),
        ];

        // Load specific data based on tab
        switch ($tab) {
            case 'dashboard':
                $query = EmailMessage::where('direction', 'outbound');
                $meetingQuery = Meeting::whereDate('start_time', now());
                
                if (!$isAdmin || $viewType === 'personal') {
                    $query->whereHas('thread.account.users', fn($q) => $q->where('user_id', $user->id));
                    $meetingQuery->where('assigned_to', $user->id);
                }

                $data['kpis'] = [
                    ['label' => 'Emails Today', 'value' => $query->whereDate('created_at', now())->count(), 'icon' => 'fas fa-paper-plane', 'trend' => 12, 'bgColor' => 'bg-blue-50', 'textColor' => 'text-blue-600'],
                    ['label' => 'Meetings', 'value' => $meetingQuery->count(), 'icon' => 'fas fa-calendar-check', 'trend' => -5, 'bgColor' => 'bg-purple-50', 'textColor' => 'text-purple-600'],
                    ['label' => 'Active Journeys', 'value' => CampaignJourney::where('status', 'active')->count(), 'icon' => 'fas fa-route', 'trend' => 0, 'bgColor' => 'bg-orange-50', 'textColor' => 'text-orange-600'],
                    ['label' => 'Inbound Queue', 'value' => EmailMessage::where('direction', 'inbound')->whereNull('read_at')
                        ->when(!$isAdmin || $viewType === 'personal', fn($q) => $q->whereHas('thread.account', fn($sq) => $sq->where('user_id', $user->id)))
                        ->count(), 'icon' => 'fas fa-inbox', 'trend' => 5, 'bgColor' => 'bg-emerald-50', 'textColor' => 'text-emerald-600'],
                ];

                $data['agenda'] = $meetingQuery->with('trackable')
                    ->orderBy('start_time')
                    ->get()
                    ->map(fn($m) => [
                        'id' => $m->id,
                        'time' => $m->start_time->format('h:i A'),
                        'title' => $m->title,
                        'type' => $m->location ? 'In-Person' : 'Virtual',
                        'person' => $m->trackable->name ?? $m->trackable->first_name ?? 'Unknown',
                        'link' => $m->conferencing_link
                    ]);

                // Signals logic
                $signalQuery = EmailMessage::with(['thread.trackable', 'thread.account.users'])->latest()->limit(10);
                if (!$isAdmin || $viewType === 'personal') {
                    $signalQuery->whereHas('thread.account.users', fn($q) => $q->where('user_id', $user->id));
                }
                $data['signals'] = $signalQuery->get()->map(fn($m) => [
                            'id' => $m->id,
                            'title' => ($m->thread->account->users->first()->name ?? 'Shared Team') . ' ' . ($m->direction === 'inbound' ? 'received' : 'sent') . ' to ' . ($m->thread->trackable->name ?? $m->recipient),
                            'time' => $m->created_at->diffForHumans(),
                            'icon' => $m->direction === 'inbound' ? 'fa-envelope-open' : 'fa-paper-plane',
                            'typeColor' => $m->direction === 'inbound' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600'
                        ]);
                break;

            case 'analytics':
                $sentQuery = EmailMessage::where('direction', 'outbound');
                $statQuery = EmailStat::query();

                if (!$isAdmin || $viewType === 'personal') {
                    $sentQuery->whereHas('thread.account.users', fn($q) => $q->where('user_id', $user->id));
                    $statQuery->whereHas('message.thread.account.users', fn($q) => $q->where('user_id', $user->id));
                }

                $totalSent = (clone $sentQuery)->count() ?: 1;
                $totalOpened = (clone $statQuery)->where('event_type', 'open')->distinct('message_id')->count();
                $totalClicked = (clone $statQuery)->where('event_type', 'click')->distinct('message_id')->count();
                $totalReplied = (clone $statQuery)->where('event_type', 'reply')->distinct('message_id')->count();

                $data['funnel'] = [
                    ['label' => 'Sent', 'value' => $totalSent, 'percentage' => 100, 'color' => 'bg-slate-500'],
                    ['label' => 'Opened', 'value' => $totalOpened, 'percentage' => round(($totalOpened / $totalSent) * 100, 1), 'color' => 'bg-indigo-500'],
                    ['label' => 'Clicked', 'value' => $totalClicked, 'percentage' => round(($totalClicked / $totalSent) * 100, 1), 'color' => 'bg-orange-500'],
                    ['label' => 'Replied', 'value' => $totalReplied, 'percentage' => round(($totalReplied / $totalSent) * 100, 1), 'color' => 'bg-emerald-500'],
                ];

                $data['leaderboard'] = \App\Models\User::has('crmEmailAccounts')
                    ->withCount(['crmEmails as activities' => fn($q) => $q->where('direction', 'outbound')])
                    ->orderByDesc('activities')
                    ->limit(5)
                    ->get()->map(fn($u) => ['name' => $u->name, 'score' => $u->activities, 'rate' => '85%']);
                break;

            case 'client360':
                $search = $request->input('search');
                $clientId = $request->input('contact_id');
                if ($search) {
                    $data['searchResults'] = Contact::where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->limit(10)->get();
                }
                if ($clientId) {
                    $contact = Contact::find($clientId);
                    if ($contact) {
                        $data['client'] = $contact;
                        $emails = EmailMessage::whereHas('thread', fn($q) => $q->where('trackable_type', Contact::class)->where('trackable_id', $contact->id))->latest()->limit(50)->get();
                        $meetings = Meeting::where('trackable_type', Contact::class)->where('trackable_id', $contact->id)->latest()->get();
                        $data['timeline'] = collect($emails)->map(fn($m) => [
                            'id' => 'email_' . $m->id, 'type' => 'Email', 'icon' => $m->direction === 'inbound' ? 'fa-envelope-open' : 'fa-paper-plane',
                            'typeColor' => $m->direction === 'inbound' ? 'border-emerald-200 text-emerald-600' : 'border-blue-200 text-blue-600',
                            'date' => $m->created_at->diffForHumans(), 'timestamp' => $m->created_at,
                            'title' => ($m->direction === 'inbound' ? 'Received: ' : 'Sent: ') . ($m->thread->subject ?? 'No Subject'), 'content' => strip_tags($m->body_text ?? $m->body_html),
                        ])->concat(collect($meetings)->map(fn($m) => [
                            'id' => 'meeting_' . $m->id, 'type' => 'Meeting', 'icon' => 'fa-calendar-check', 'typeColor' => 'border-purple-200 text-purple-600',
                            'date' => $m->start_time->diffForHumans(), 'timestamp' => $m->start_time, 'title' => $m->title, 'content' => $m->description,
                        ]))->sortByDesc('timestamp')->values()->all();
                    }
                }
                break;

            case 'inbox':
            case 'sent':
            case 'drafts':
            case 'communications':
                $data['accounts'] = EmailAccount::whereHas('users', fn($q) => $q->where('user_id', $user->id))->get()->map(fn($acc) => [
                    'id' => $acc->id, 'email' => $acc->email_address, 'provider' => ucfirst($acc->provider)
                ]);
                $data['users'] = \App\Models\User::all(['id', 'name']);

                // Build thread query
                $userAccountIds = EmailAccount::whereHas('users', fn($q) => $q->where('user_id', $user->id))->pluck('id');
                $specificAccountId = $request->input('account_id');
                $data['selected_account_id'] = $specificAccountId;

                $query = EmailThread::where('tenant_id', $user->tenant_id);

                if ($specificAccountId) {
                    $query->where('email_account_id', $specificAccountId);
                } elseif (!$isAdmin || $viewType === 'personal') {
                    $query->whereIn('email_account_id', $userAccountIds);
                }

                if ($tab === 'sent') {
                    $query->whereHas('messages', fn($q) => $q->where('direction', 'outbound')->where('status', 'sent'));
                } elseif ($tab === 'drafts') {
                    $query->whereHas('messages', fn($q) => $q->where('status', 'draft'));
                } elseif ($tab === 'inbox' || $tab === 'communications') {
                    $query->whereHas('messages', fn($q) => $q->where('direction', 'inbound'));
                }

                $data['threads'] = $query
                    ->with([
                        'messages' => fn($q) => $q->latest()->limit(1),
                        'trackable',
                        'account'
                    ])
                    ->latest()
                    ->paginate(25);
                break;


            case 'meetings':
            case 'calendar':
                $data['meetings'] = Meeting::where('tenant_id', $user->tenant_id)->where('assigned_to', $user->id)->with('trackable')->latest()->get();
                break;

            case 'all_meetings':
                $data['meetings'] = Meeting::where('tenant_id', $user->tenant_id)->with(['trackable', 'user'])->latest()->paginate(20);
                break;

            case 'campaigns':
                $data['campaigns'] = CampaignJourney::where('tenant_id', $user->tenant_id)->latest()->get();
                break;

            case 'automations':
                $data['rules'] = \App\Models\CRM\AutomationRule::where('tenant_id', $user->tenant_id)->get();
                break;

            case 'settings':
            case 'assignments':
                $accQuery = EmailAccount::query();
                if (!$isAdmin) {
                    $accQuery->whereHas('users', fn($q) => $q->where('user_id', $user->id));
                }
                $data['accounts'] = $accQuery->with('users')->get()->map(fn($acc) => [
                    'id' => $acc->id, 
                    'email' => $acc->email_address, 
                    'provider' => ucfirst($acc->provider), 
                    'is_active' => $acc->is_active,
                    'user_ids' => $acc->users->pluck('id')->toArray(),
                    'user_names' => $acc->users->pluck('name')->toArray(),
                    'last_synced_at' => $acc->last_synced_at ? $acc->last_synced_at->diffForHumans() : null,
                ]);
                $data['users'] = \App\Models\User::all(['id', 'name']);
                break;

            case 'governance':
                $data['roles'] = \App\Models\Role::where('tenant_id', $user->tenant_id)->with('permissions')->get();
                $data['permissions'] = \App\Models\Permission::where('module', 'CRM')->get();
                $data['users'] = \App\Models\User::where('tenant_id', $user->tenant_id)->with('roles')->get(['id', 'name', 'email']);
                break;
        }

        return Inertia::render('CRM/CommunicationHub', $data);
    }

    /**
     * Transfer a thread or contact to another user.
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'type' => 'required|in:thread,contact',
            'id' => 'required|integer',
            'user_id' => 'required|exists:users,id',
            'options' => 'nullable|array',
        ]);

        $options = $request->options ?? [
            'activities' => false,
            'notifyClient' => false,
            'keepHistory' => true,
        ];

        $tenantId = auth()->user()->tenant_id;
        $fromUserId = null;
        $entityType = null;
        $entityIds = [];

        if ($request->type === 'thread') {
            $thread = EmailThread::findOrFail($request->id);
            $fromUserId = auth()->id();
            
            // Re-map the thread to the target user's default email account
            $targetAccount = EmailAccount::whereHas('users', fn($q) => $q->where('user_id', $request->user_id))->first();
            
            $threadsToTransfer = [$thread];
            if (!empty($options['allHistory']) && $thread->trackable_type && $thread->trackable_id) {
                $threadsToTransfer = EmailThread::where('trackable_type', $thread->trackable_type)
                    ->where('trackable_id', $thread->trackable_id)
                    ->get();
                
                // If it's a contact or lead, transfer the ownership too
                if ($thread->trackable_type === Contact::class) {
                    Contact::where('id', $thread->trackable_id)->update(['owner_id' => $request->user_id]);
                }
            }

            foreach ($threadsToTransfer as $t) {
                if ($targetAccount) {
                    $t->update(['email_account_id' => $targetAccount->id]);
                }
                $entityIds[] = $t->id;
            }
            $entityType = EmailThread::class;
            
        } else {
            $contact = Contact::findOrFail($request->id);
            $fromUserId = $contact->owner_id ?? null;
            $contact->update(['owner_id' => $request->user_id]);
            $entityType = Contact::class;
            $entityIds = [$contact->id];

            if ($options['activities']) {
                \App\Models\CRM\Activity::where('activityable_type', Contact::class)
                    ->where('activityable_id', $contact->id)
                    ->update(['assigned_to' => $request->user_id]);
                
                \App\Models\CRM\Meeting::where('trackable_type', Contact::class)
                    ->where('trackable_id', $contact->id)
                    ->update(['assigned_to' => $request->user_id]);
            }
        }

        \App\Models\CRM\HandoverLog::create([
            'tenant_id' => $tenantId,
            'from_user_id' => $fromUserId ?? auth()->id(),
            'to_user_id' => $request->user_id,
            'entity_type' => class_basename($entityType),
            'entity_ids' => $entityIds,
            'transfer_options' => $options,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Transfer completed successfully.');
    }

    /**
     * Sync emails from the configured active account.
     */
    public function sync(Request $request)
    {
        $accountId = $request->input('account_id');
        $query = \App\Models\CRM\EmailAccount::query();
        
        if ($accountId) {
            $query->where('id', $accountId);
        }

        $account = $query->first();

        if (!$account) {
            return back()->with('error', 'No email account linked to sync.');
        }

        // Authorization check
        $user = auth()->user();
        $isAdmin = $user->role === 'admin' || $user->role === 'super_admin';
        if (!$isAdmin && !$account->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'You do not have permission to sync this account.');
        }

        $syncService = new \App\Services\CRM\EmailSyncService();
        $syncService->syncAccount($account);

        return back()->with('success', 'Email sync completed successfully.');
    }


    /**
     * Show a single thread with all its messages (via API).
     */
    public function showThread(EmailThread $thread)
    {
        $thread->load([
            'messages' => fn($q) => $q->oldest(), // Oldest first for the chat view
            'trackable',
            'account'
        ]);

        return response()->json($thread);
    }

    /**
     * Reply to a thread from the inbox.

     */
    public function reply(Request $request)
    {
        $request->validate([
            'thread_id' => 'required_without:is_new|integer',
            'body' => 'required|string',
            'to' => 'required_if:is_new,true|string',
            'subject' => 'required_if:is_new,true|string',
        ]);

        $user = auth()->user();
        
        if ($request->is_new) {
            $account = \App\Models\CRM\EmailAccount::whereHas('users', fn($q) => $q->where('user_id', $user->id))
                ->where('id', $request->account_id) // Priority for specified account
                ->first() ?? \App\Models\CRM\EmailAccount::whereHas('users', fn($q) => $q->where('user_id', $user->id))->first();
                
            if (!$account) {
                return back()->withErrors(['error' => 'No active email account found.']);
            }

            $thread = EmailThread::create([
                'tenant_id' => $user->tenant_id,
                'email_account_id' => $account->id,
                'subject' => $request->subject,
                'mapping_status' => 'new'
            ]);
            $toEmails = array_map('trim', explode(',', $request->to));
        } else {
            $thread = EmailThread::with(['messages', 'account'])->findOrFail($request->thread_id);
            $account = $thread->account; // Always use the thread's own account
            
            if (!$account || (!$account->users()->where('user_id', $user->id)->exists() && !$user->roles->contains('name', 'Admin'))) {
                return back()->withErrors(['error' => 'You do not have permission to reply from this account.']);
            }

            $lastMessage = $thread->messages->sortByDesc('sent_at')->first();
            $toEmails = [$lastMessage ? ($lastMessage->direction === 'inbound' ? $lastMessage->from_email : $lastMessage->to_emails[0] ?? 'client@example.com') : 'client@example.com'];
        }

        $ccEmails = $request->cc ? array_map('trim', explode(',', $request->cc)) : [];
        $bccEmails = $request->bcc ? array_map('trim', explode(',', $request->bcc)) : [];

        // Log the outbound reply
        $reply = EmailMessage::create([
            'thread_id' => $thread->id,
            'message_id' => uniqid('msg_out_'),
            'from_email' => $account->email_address,
            'to_emails' => $toEmails,
            'cc_emails' => $ccEmails,
            'bcc_emails' => $bccEmails,
            'body_html' => $request->body,
            'body_text' => strip_tags($request->body),
            'direction' => 'outbound',
            'status' => $request->is_draft ? 'draft' : 'draft', // Process status below
            'sent_at' => now(),
        ]);

        $sentStatus = 'success';
        if (!$request->is_draft) {
            $senderService = new \App\Services\CRM\EmailSenderService();
            $sentStatus = $senderService->send($account, $reply);
            
            if ($sentStatus === 'success') {
                $reply->update(['status' => 'sent', 'sent_at' => now()]);
            } else {
                 $reply->update(['status' => 'bounced']);
            }
        } else {
            $reply->update(['status' => 'draft']);
        }

        $thread->update(['updated_at' => now()]);

        if ($request->is_draft) {
            return back()->with('success', 'Draft saved successfully.');
        }

        if ($sentStatus === 'success') {
            return back()->with('success', 'Message sent successfully.');
        }

        return back()->with('error', 'Message failed to send: ' . $sentStatus);
    }



    protected function getTabs()
    {
        return [
            ['id' => 'dashboard', 'name' => 'Command Center', 'icon' => 'fas fa-th-large'],
            ['id' => 'communications', 'name' => 'Inbox & Chats', 'icon' => 'fas fa-envelope'],
            ['id' => 'drafts', 'name' => 'Drafts', 'icon' => 'fas fa-edit'],
            ['id' => 'analytics', 'name' => 'Analytics & Reports', 'icon' => 'fas fa-chart-pie'],
            ['id' => 'client360', 'name' => 'Client 360° Search', 'icon' => 'fas fa-search'],
            ['id' => 'meetings', 'name' => 'Meetings & Calendar', 'icon' => 'fas fa-calendar-alt'],
            ['id' => 'settings', 'name' => 'Sync Settings', 'icon' => 'fas fa-cog'],
        ];
    }

}
