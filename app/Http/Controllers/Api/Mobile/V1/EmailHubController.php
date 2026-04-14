<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\EmailThread;
use App\Models\CRM\EmailMessage;
use App\Models\CRM\EmailAccount;
use App\Services\CRM\EmailSenderService;
use App\Services\Infrastructure\LoggerService;

class EmailHubController extends Controller
{
    /**
     * Get Conversations List (Chat interface for emails)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $threads = EmailThread::with(['messages' => fn($q) => $q->latest()->limit(1), 'account'])
            ->whereHas('account.users', fn($q) => $q->where('user_id', $user->id))
            ->latest('updated_at')
            ->paginate(20);

        return response()->json($threads);
    }

    /**
     * Get Single Conversation Messages
     */
    public function show(EmailThread $thread)
    {
        $user = auth()->user();
        if (!$thread->account->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'Unauthorized.');
        }

        $thread->load(['messages' => fn($q) => $q->oldest(), 'account']);

        return response()->json($thread);
    }

    /**
     * Send Message (Reply in Chat)
     */
    public function reply(Request $request, EmailThread $thread)
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $user = auth()->user();
        $account = $thread->account;

        if (!$account->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'Unauthorized.');
        }

        $lastMessage = $thread->messages()->latest()->first();
        $toEmails = [$lastMessage ? ($lastMessage->direction === 'inbound' ? $lastMessage->from_email : $lastMessage->to_emails[0]) : 'client@example.com'];

        $message = EmailMessage::create([
            'thread_id' => $thread->id,
            'message_id' => uniqid('msg_mob_'),
            'from_email' => $account->email_address,
            'to_emails' => $toEmails,
            'body_html' => $request->body,
            'body_text' => strip_tags($request->body),
            'direction' => 'outbound',
            'status' => 'pending',
            'sent_at' => now(),
        ]);

        $senderService = new EmailSenderService();
        $status = $senderService->send($account, $message);

        if ($status === 'success') {
            $message->update(['status' => 'sent']);
            $thread->touch();
        }

        return response()->json([
            'success' => $status === 'success',
            'message' => $status === 'success' ? 'Message sent.' : 'Failed to send.',
            'chat_message' => $message
        ]);
    }

    /**
     * Get Team Signals (Sharing Room)
     * Aggregates recent activity across the platform for a collaborative feed.
     */
    public function signals(Request $request)
    {
        $user = $request->user();
        
        // 1. Fetch recent Email interactions (Signals)
        $comms = EmailMessage::with(['thread.account'])
            ->whereHas('thread.account.users', fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($m) => [
                'id' => 'comm_' . $m->id,
                'type' => 'communication',
                'title' => ($m->direction === 'inbound' ? 'Email received from ' : 'Email sent to ') . ($m->thread->subject ?? 'External'),
                'content' => substr(strip_tags($m->body_text ?? $m->body_html), 0, 100) . '...',
                'user' => $m->from_email,
                'time' => $m->created_at->diffForHumans(),
                'icon' => 'fas fa-envelope'
            ]);

        // 2. Fetch recent Task updates
        $tasks = \App\Models\Task::whereHas('assignees', fn($q) => $q->where('employee_id', $user->employee->id ?? 0))
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'id' => 'task_' . $t->id,
                'type' => 'task',
                'title' => 'Task Updated: ' . $t->title,
                'content' => 'Progress: ' . $t->progress . '% | Status: ' . $t->status,
                'user' => 'System',
                'time' => $t->updated_at->diffForHumans(),
                'icon' => 'fas fa-tasks'
            ]);

        // Combine and Sort
        $allSignals = $comms->concat($tasks)
            ->sortByDesc('time')
            ->values();

        return response()->json($allSignals);
    }
    /**
     * Compose New Email (Gmail Style)
     */
    public function compose(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:email_accounts,id',
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $user = auth()->user();
        $account = EmailAccount::findOrFail($request->account_id);

        if (!$account->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'Unauthorized.');
        }

        // 1. Create Thread
        $thread = EmailThread::create([
            'account_id' => $account->id,
            'subject' => $request->subject,
            'status' => 'active',
            'last_message_at' => now(),
        ]);

        // 2. Create Message
        $message = EmailMessage::create([
            'thread_id' => $thread->id,
            'message_id' => uniqid('msg_mob_'),
            'from_email' => $account->email_address,
            'to_emails' => [$request->to],
            'subject' => $request->subject,
            'body_html' => $request->body,
            'body_text' => strip_tags($request->body),
            'direction' => 'outbound',
            'status' => 'pending',
            'sent_at' => now(),
        ]);

        // 3. Dispatch
        try {
            $senderService = new EmailSenderService();
            $status = $senderService->send($account, $message);
            
            if ($status === 'success') {
                $message->update(['status' => 'sent']);
            }
            
            \Log::context(['user_id' => $user->id, 'action' => 'mobile_compose_email']);
            LoggerService::info('Mobile Email Sent', ['to' => $request->to, 'subject' => $request->subject]);

            return response()->json([
                'success' => $status === 'success',
                'message' => 'Email sent matrix successfully.',
                'thread_id' => $thread->id
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
