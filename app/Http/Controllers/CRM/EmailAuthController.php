<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\EmailAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailAuthController extends Controller
{
    /**
     * Redirect to OAuth provider.
     */
    public function redirect(Request $request, string $provider)
    {
        $tenantId = auth()->user()->tenant_id;
        
        if ($provider === 'google') {
            $query = http_build_query([
                'client_id' => config('services.google.client_id'),
                'redirect_uri' => route('crm.comms.auth.callback', ['provider' => 'google']),
                'response_type' => 'code',
                // For reading/sending email, we request these scopes
                'scope' => 'https://www.googleapis.com/auth/gmail.readonly https://www.googleapis.com/auth/gmail.send',
                'access_type' => 'offline',
                'prompt' => 'consent',
                'state' => $tenantId,
            ]);
            return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
        }

        if ($provider === 'outlook') {
            $query = http_build_query([
                'client_id' => config('services.outlook.client_id'),
                'redirect_uri' => route('crm.comms.auth.callback', ['provider' => 'outlook']),
                'response_type' => 'code',
                // Microsoft Graph scopes
                'scope' => 'offline_access User.Read Mail.Read Mail.Send',
                'prompt' => 'consent',
                'state' => $tenantId,
            ]);
            $tenant = config('services.outlook.tenant', 'common');
            return redirect("https://login.microsoftonline.com/$tenant/oauth2/v2.0/authorize?" . $query);
        }
        
        return back()->with('error', 'Provider not supported');
    }

    /**
     * Handle OAuth callback.
     */
    public function callback(Request $request, string $provider)
    {
        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('crm.comms.hub', ['tab' => 'settings'])->with('error', 'Auth failed or access denied.');
        }

        try {
            $email = 'unknown@example.com';
            $tokens = [];
            $expiresIn = 3600;

            if ($provider === 'google') {
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'code' => $code,
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'redirect_uri' => route('crm.comms.auth.callback', ['provider' => 'google']),
                    'grant_type' => 'authorization_code',
                ]);
                
                if (!$response->successful()) {
                    throw new \Exception('Google token exchange failed: ' . $response->body());
                }
                $tokens = $response->json();
                $expiresIn = $tokens['expires_in'] ?? 3600;

                // get user profile
                $userResponse = Http::withToken($tokens['access_token'])->get('https://www.googleapis.com/oauth2/v2/userinfo');
                if ($userResponse->successful()) {
                    $email = $userResponse->json()['email'] ?? $email;
                }
            } elseif ($provider === 'outlook') {
                $tenant = config('services.outlook.tenant', 'common');
                $response = Http::asForm()->post("https://login.microsoftonline.com/$tenant/oauth2/v2.0/token", [
                    'code' => $code,
                    'client_id' => config('services.outlook.client_id'),
                    'client_secret' => config('services.outlook.client_secret'),
                    'redirect_uri' => route('crm.comms.auth.callback', ['provider' => 'outlook']),
                    'grant_type' => 'authorization_code',
                ]);
                
                if (!$response->successful()) {
                    throw new \Exception('Outlook token exchange failed: ' . $response->body());
                }
                $tokens = $response->json();
                $expiresIn = $tokens['expires_in'] ?? 3600;

                // get user profile via Microsoft Graph
                $userResponse = Http::withToken($tokens['access_token'])->get('https://graph.microsoft.com/v1.0/me');
                if ($userResponse->successful()) {
                    $email = $userResponse->json()['mail'] ?? $userResponse->json()['userPrincipalName'] ?? $email;
                }
            }

            if (empty($tokens['access_token'])) {
                throw new \Exception('Access token missing from provider response.');
            }
            
            // Save or update account
            $account = EmailAccount::updateOrCreate(
                [
                    'email_address' => $email,
                    'provider' => $provider,
                ],
                [
                    'tenant_id' => auth()->user()->tenant_id,
                    'credentials' => [
                        'access_token' => $tokens['access_token'],
                        'refresh_token' => $tokens['refresh_token'] ?? null,
                        'expires_in' => $expiresIn,
                        'created_at' => time(),
                    ],
                    'is_active' => true,
                ]
            );

            // Many-to-many link: ensure the current user has access
            $account->users()->syncWithoutDetaching([auth()->id()]);

            return redirect()->route('crm.comms.hub', ['section' => 'communications', 'tab' => 'settings'])->with('success', ucfirst($provider) . ' account linked successfully!');

        } catch (\Exception $e) {
            Log::error("OAuth Callback Error: " . $e->getMessage());
            return redirect()->route('crm.comms.hub', ['section' => 'communications', 'tab' => 'settings'])->with('error', 'Auth failed: ' . $e->getMessage());
        }
    }

    /**
     * Store and validate IMAP configuration securely.
     */
    public function storeImap(Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|numeric',
            'email' => 'required|email',
            'password' => 'required|string',
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|numeric',
        ]);

        try {
            if (function_exists('imap_open')) {
                // Determine security protocol flag based on port
                $encryption = ($request->port == 993 || $request->port == 995) ? '/imap/ssl/novalidate-cert' : '/imap/notls';
                $server = '{' . $request->host . ':' . $request->port . $encryption . '}INBOX';
                
                $imap = @imap_open($server, $request->email, $request->password, OP_HALFOPEN);
                
                if (!$imap) {
                    $errors = imap_errors();
                    return back()->withErrors(['email' => 'Failed to authenticate IMAP. Check credentials. Errs: ' . json_encode($errors)]);
                }
                @imap_close($imap);
            }

            // Store credentials securely inside the encrypted JSON column 'credentials'.
            $account = EmailAccount::updateOrCreate(
                [
                    'email_address' => $request->email,
                    'provider' => 'imap',
                ],
                [
                    'tenant_id' => auth()->user()->tenant_id,
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

            // Sync user connection
            $account->users()->syncWithoutDetaching([auth()->id()]);


            return back()->with('success', 'IMAP Account validated and securely linked.');

        } catch (\Exception $e) {
            Log::error("IMAP Secure Connection Error: " . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to connect securely. Exception: ' . $e->getMessage()]);
        }
    }

    public function toggleActive(\App\Models\CRM\EmailAccount $account)
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin' || $user->role === 'super_admin';
        
        // Authorization check: Admin or an assigned user
        if (!$isAdmin && !$account->users()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $account->update(['is_active' => !$account->is_active]);
        return back()->with('success', 'Account status updated.');
    }

    public function assignUsers(Request $request, \App\Models\CRM\EmailAccount $account)
    {
        $isAdmin = auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin';
        if (!$isAdmin) {
            abort(403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $account->users()->sync($request->user_ids);
        return back()->with('success', 'Account handlers synchronized successfully.');
    }

    public function unlink(\App\Models\CRM\EmailAccount $account)
    {
        $isAdmin = auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin';
        if (!$isAdmin) {
            abort(403, 'Only admins can unlink accounts.');
        }

        $account->delete();
        return back()->with('success', 'Email account unlinked from the system.');
    }
}

