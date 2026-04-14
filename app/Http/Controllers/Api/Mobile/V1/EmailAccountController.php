<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\EmailAccount;
use App\Http\Controllers\CRM\EmailAuthController;
use App\Services\Infrastructure\LoggerService;

class EmailAccountController extends Controller
{
    /**
     * List user-linked email accounts
     */
    public function index(Request $request)
    {
        $accounts = EmailAccount::whereHas('users', fn($q) => $q->where('user_id', $request->user()->id))
            ->get(['id', 'email_address', 'provider', 'is_active', 'last_synced_at']);
            
        return response()->json($accounts);
    }

    /**
     * Store (Link) IMAP/SMTP Account
     */
    public function store(Request $request)
    {
        // We reuse the logic from EmailAuthController@storeImap
        $authController = new EmailAuthController();
        
        // This validates and stores the account
        // Note: In a real scenario, we'd refactor the service logic out, 
        // but for speed we'll simulate the response or call the method.
        
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|numeric',
            'email' => 'required|email',
            'password' => 'required|string',
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|numeric',
        ]);

        try {
            // Logic mirrored from EmailAuthController
            $account = EmailAccount::updateOrCreate(
                [
                    'email_address' => $request->email,
                    'provider' => 'imap',
                ],
                [
                    'tenant_id' => $request->user()->tenant_id,
                    'credentials' => [
                        'host' => $request->host,
                        'port' => $request->port,
                        'smtp_host' => $request->smtp_host ?: $request->host,
                        'smtp_port' => $request->smtp_port ?: 465,
                        'password' => $request->password,
                        'protocol' => 'imap',
                        'encryption' => 'ssl'
                    ],
                    'is_active' => true,
                ]
            );

            $account->users()->syncWithoutDetaching([$request->user()->id]);
            
            \Log::context(['user_id' => $request->user()->id, 'action' => 'mobile_smtp_link']);
            LoggerService::info("SMTP Account linked via Mobile", ['email' => $request->email]);

            return response()->json([
                'success' => true,
                'message' => 'Account linked successfully.',
                'account' => $account
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to link account: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Toggle Account Active Status
     */
    public function toggle(EmailAccount $account)
    {
        if (!$account->users()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        $account->update(['is_active' => !$account->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $account->is_active
        ]);
    }
}
