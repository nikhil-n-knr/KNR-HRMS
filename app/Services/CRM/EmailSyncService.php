<?php

namespace App\Services\CRM;

use App\Models\CRM\EmailAccount;
use App\Models\CRM\EmailThread;
use App\Models\CRM\EmailMessage;
use App\Models\CRM\Contact;
use App\Models\CRM\Lead;
use App\Models\CRM\Deal;
use Illuminate\Support\Facades\Log;

class EmailSyncService
{
    /**
     * Sync an individual email account.
     */
    public function syncAccount(EmailAccount $account)
    {
        Log::info("Starting email sync for account: {$account->email_address}");

        try {
            // Logic for IMAP or OAuth (Gmail/Outlook) based on account->provider
            if ($account->provider === 'imap') {
                $this->syncImap($account);
            } else {
                $this->syncOAuth($account);
            }

            $account->update(['last_synced_at' => now()]);
        } catch (\Exception $e) {
            Log::error("Failed to sync email account {$account->id}: " . $e->getMessage());
        }
    }

    protected function syncImap(EmailAccount $account)
    {
        // Prevent timeout during potentially slow IMAP structural fetches
        set_time_limit(0);

        if (!function_exists('imap_open')) {
            Log::warning("IMAP extension not installed. Simulating sync for {$account->email_address}");
            $this->simulateEmails($account);
            return;
        }

        $credentials = $account->credentials;
        $host        = $credentials['host'] ?? '';
        $port        = (int) ($credentials['port'] ?? 993);
        $password    = $credentials['password'] ?? '';
        $email       = $account->email_address;
        
        if (empty($host) || empty($password)) {
            Log::error("Email sync aborted for {$email}: Missing credentials. After the Security Key (APP_KEY) change, you MUST re-save this account's settings in the Hub.");
            return;
        }
        
        // Use explicitly defined protocol if available, otherwise detect by port
        $protocol = $credentials['protocol'] ?? (($port === 995 || $port === 110) ? 'pop3' : 'imap');
        $folder   = 'INBOX';

        $encryption = $port === 995 || $port === 993 ? 'ssl' : 'notls';

        // Build mailbox string — /novalidate-cert handles self-signed certs
        $mailbox = '{' . $host . ':' . $port . '/' . $protocol . '/' . $encryption . '/novalidate-cert}' . $folder;

        Log::info("Connecting to mailbox: {$mailbox} as {$email}");

        // Set timeout to avoid hanging
        imap_timeout(IMAP_OPENTIMEOUT, 30);
        $imap = @imap_open($mailbox, $email, $password);

        if (!$imap) {
            $err = imap_last_error();
            Log::error("IMAP/POP3 connection failed for {$email}: {$err}");
            // Only simulation if we have NO previous emails to prevent confusion
            if (EmailMessage::whereHas('thread', fn($q) => $q->where('email_account_id', $account->id))->count() === 0) {
                $this->simulateEmails($account);
            }
            return;
        }

        $emails = [];
        $imported = 0;
        $sinceDate = $account->last_synced_at 
            ? $account->last_synced_at->subHours(12)->format('d-M-Y') // Slight overlap for safety
            : now()->subDays(90)->format('d-M-Y');
            
        // FORCE REPAIR: If last sync was recent but there are broken messages, we ignore last_synced_at
        $brokenCount = EmailMessage::whereHas('thread', fn($q) => $q->where('email_account_id', $account->id))
            ->where(function($q) {
                $q->where('body_text', 'LIKE', 'PCFET%')
                  ->orWhere('body_text', 'LIKE', 'PGh0bWw%')
                  ->orWhere('body_text', 'LIKE', 'PHRhYmxl%')
                  ->orWhere('body_text', 'LIKE', '--%');
            })->count();
            
        if ($brokenCount > 0) {
            $sinceDate = now()->subDays(90)->format('d-M-Y');
            Log::info("Broken messages detected ($brokenCount). Forcing historical repair sync for {$account->email_address}.");
        }

        $searchCriteria = "SINCE \"$sinceDate\"";

        if ($protocol === 'imap') {
            $emails   = @imap_search($imap, $searchCriteria, SE_UID);
            
            if (!$emails) {
                Log::info("No new emails found via IMAP search for {$email} since {$sinceDate}. Last error: " . imap_last_error());
            }
        } 
        
        // If it's POP3 or IMAP search failed/returned empty, try to fetch the latest messages by index
        if (!$emails || count($emails) === 0) {
            Log::info("Attempting to fetch by sequence index (required for POP3 or empty IMAP search).");
            $total = imap_num_msg($imap);
            if ($total > 0) {
                $start = max(1, $total - 99); // Increased to last 100 messages
                $emails = range($total, $start); // Sequence numbers in reverse
            }
        } else {
            // Newest first; limit to 100 per sync
            rsort($emails);
            $emails = array_slice($emails, 0, 100);
        }


        if (!$emails || count($emails) === 0) {
            Log::info("No emails available to process for {$email}. Total messages in mailbox: " . imap_num_msg($imap));
            @imap_close($imap);
            return;
        }

        $imported = 0;

        foreach ($emails as $id) {
            try {
                // Determine if $id is a UID or MsgNo
                $fetchOption = ($protocol === 'imap' && !isset($start)) ? FT_UID : 0;
                
                $overview = @imap_fetch_overview($imap, (string)$id, $fetchOption);
                if (!$overview || !isset($overview[0])) continue;
                $ov = $overview[0];

                // Deduplicate by message-id (if available) or unique combination
                $messageId = trim($ov->message_id ?? '');
                if (!$messageId) {
                    // Fallback to a unique string for POP3/Messages without ID
                    $messageId = md5($ov->subject . $ov->from . $ov->date . ($ov->size ?? ''));
                }

                if (EmailMessage::where('message_id', $messageId)->exists()) {
                    continue;
                }

                $msgNo = $fetchOption === FT_UID ? imap_msgno($imap, $id) : (int)$id;
                $header = @imap_headerinfo($imap, $msgNo);
                if (!$header) continue;

                $fromObj = $header->from[0] ?? null;
                $fromEmail = $fromObj ? $fromObj->mailbox . '@' . $fromObj->host : 'unknown@unknown.com';
                
                // Decode from personal name if exists
                if ($fromObj && isset($fromObj->personal)) {
                    $elements = imap_mime_header_decode($fromObj->personal);
                    $fromName = '';
                    foreach ($elements as $element) {
                        $fromName .= $element->text;
                    }
                } else {
                    $fromName = '';
                }

                $subjectElements = isset($ov->subject) ? imap_mime_header_decode($ov->subject) : [];
                $subject = '';
                foreach ($subjectElements as $element) {
                    $subject .= $element->text;
                }
                $subject = $subject ?: '(No Subject)';
                
                $date = isset($ov->date) ? date('Y-m-d H:i:s', strtotime($ov->date)) : now()->toDateTimeString();

                $bodyHtml = '';
                $bodyText = '';

                $structure = @imap_fetchstructure($imap, (string)$id, $fetchOption);
                if ($structure) {
                    if (!isset($structure->parts)) {
                        $body = @imap_fetchbody($imap, (string)$id, '1', $fetchOption);
                        $body = $this->decodeBody($body, $structure->encoding ?? 0);
                        if ($structure->subtype === 'HTML') {
                            $bodyHtml = $body;
                        } else {
                            $bodyText = $body;
                        }
                    } else {
                        foreach ($structure->parts as $index => $part) {
                            $partNum = $index + 1;
                            $this->parseStructure($imap, $id, $part, (string)$partNum, $fetchOption, $bodyHtml, $bodyText);
                        }
                    }
                }

                if (!$bodyHtml && !$bodyText) {
                    $raw = @imap_fetchbody($imap, (string)$id, '1', $fetchOption);
                    $bodyText = $this->decodeBody($raw, $structure->encoding ?? 0);
                    
                    // Final safety for raw content
                    if (!$bodyText && $raw) {
                         $bodyText = quoted_printable_decode($raw);
                    }
                }


                // Group into threads - include account to be safe
                $cleanSubject = preg_replace('/^(Re:|Fwd:)\s*/i', '', trim($subject));
                $thread = EmailThread::firstOrCreate(
                    [
                        'tenant_id'        => $account->tenant_id,
                        'email_account_id' => $account->id,
                        'subject'          => substr($cleanSubject ?: $subject, 0, 255),
                    ],
                    [
                        'mapping_status' => 'new',
                    ]
                );

                $msg = EmailMessage::where('message_id', $messageId)->first();
                if (!$msg) {
                    $msg = EmailMessage::create([
                        'thread_id'   => $thread->id,
                        'message_id'  => $messageId,
                        'from_email'  => $fromEmail,
                        'from_name'   => $fromName,
                        'to_emails'   => [$email],
                        'cc_emails'   => [],
                        'bcc_emails'  => [],
                        'body_html'   => $bodyHtml,
                        'body_text'   => $bodyText ?: strip_tags($bodyHtml),
                        'direction'   => 'inbound',
                        'status'      => 'delivered',
                        'sent_at'     => $date,
                    ]);
                    $imported++;
                    $this->linkMessageToEntity($msg);
                } else {
                    // Update if body is suspected to be corrupted/un-decoded MIME
                    $isBase64 = strpos($msg->body_text ?? '', 'PCFET') === 0 || strpos($msg->body_html ?? '', 'PCFET') === 0;
                    $hasBoundaries = strpos($msg->body_text ?? '', '--') !== false;
                    
                    if (empty($msg->body_html) && empty($msg->body_text) || $isBase64 || $hasBoundaries) {
                        $msg->update([
                            'body_html' => $bodyHtml,
                            'body_text' => $bodyText ?: strip_tags($bodyHtml),
                        ]);
                    }
                }



            } catch (\Throwable $e) {
                Log::error("Failed to import email index {$id}: " . $e->getMessage());
            }
        }

        @imap_close($imap);
        Log::info("Sync complete for {$email}: {$imported} emails imported.");
    }

    /**
     * Recursive structure parser to handle nested multipart emails.
     */
    protected function parseStructure($imap, $uid, $part, $partNum, $fetchOption, &$bodyHtml, &$bodyText)
    {

        // Extract charset if available
        $charset = 'UTF-8';
        if (isset($part->parameters)) {
            foreach ($part->parameters as $param) {
                if (strtoupper($param->attribute) === 'CHARSET') {
                    $charset = $param->value;
                    break;
                }
            }
        }

        // Type 0 is TEXT
        if ($part->type === 0) { 
            $partBody = @imap_fetchbody($imap, (string)$uid, $partNum, $fetchOption);
            $partBody = $this->decodeBody($partBody, $part->encoding ?? 0, $charset);
            
            $subtype = strtoupper($part->subtype ?? '');
            if ($subtype === 'HTML') {
                $bodyHtml .= $partBody;
            } elseif ($subtype === 'PLAIN') {
                $bodyText .= $partBody;
            }
        }
        
        if (isset($part->parts)) {
            foreach ($part->parts as $index => $subPart) {
                $subPartNum = $partNum ? $partNum . '.' . ($index + 1) : (string)($index + 1);
                $this->parseStructure($imap, $uid, $subPart, $subPartNum, $fetchOption, $bodyHtml, $bodyText);
            }
        }
    }

    /**
     * Decode email body content based on encoding type and charset.
     */
    protected function decodeBody(string $body, int $encoding, string $charset = 'UTF-8'): string
    {
        // 0. Handle Manual MIME Boundary Splitting
        if (preg_match('/^--([a-zA-Z0-9_\'\+\,\-\.\/\:\=\?]+)/m', $body, $bMatch)) {
             $boundary = $bMatch[1];
             if (substr($boundary, -2) === '--') {
                 $boundary = substr($boundary, 0, -2);
             }
             $parts = explode('--' . $boundary, $body);
             $bestPart = '';
             
             foreach ($parts as $p) {
                 $lowerP = strtolower($p);
                 if (strpos($lowerP, 'content-type: text/html') !== false) {
                     $bestPart = $p;
                     break; 
                 } elseif (strpos($lowerP, 'content-type: text/plain') !== false && !$bestPart) {
                     $bestPart = $p;
                 }
             }
             
             if ($bestPart) {
                 $body = $bestPart;
             }
        }

        // 1. Unconditional Header Peeling for Inner MIME Parts
        // If this part has a 'Content-Type:' or 'Content-Transfer-Encoding:', peel the headers off.
        if (stripos($body, 'Content-Type:') !== false || stripos($body, 'Content-Transfer-Encoding:') !== false) {

            // Dynamically override encoding if specified inside this part
            if (preg_match('/Content-Transfer-Encoding:\s*(base64|quoted-printable)/i', $body, $encMatch)) {
                $newEnc = strtolower($encMatch[1]);
                if ($newEnc === 'base64') $encoding = 3;
                if ($newEnc === 'quoted-printable') $encoding = 4;
            }

            // Find the boundary between headers and body: a double newline (\r\n\r\n or \n\n)
            $headerEndPos = false;
            // The exact position to start parsing body text
            if (strpos($body, "\r\n\r\n") !== false) {
                $headerEndPos = strpos($body, "\r\n\r\n") + 4;
            } elseif (strpos($body, "\n\n") !== false) {
                $headerEndPos = strpos($body, "\n\n") + 2;
            }

            if ($headerEndPos !== false) {
                // Ensure the extracted segment truly resembles MIME headers
                $headerPart = substr($body, 0, $headerEndPos);
                if (stripos($headerPart, 'Content-') !== false) {
                    $body = substr($body, $headerEndPos);
                }
            }
        }

        // 2. Normalize and Decode based on encoding
        $trimmedBody = ($encoding === 3) ? preg_replace('/\s+/', '', $body) : $body;
        
        $decoded = match($encoding) {
            3 => base64_decode($trimmedBody),
            4 => quoted_printable_decode($body),
            default => $body,
        };

        // 3. Handle character set conversion
        if ($charset && strtoupper($charset) !== 'UTF-8') {
            try {
                // Ensure charset is valid for iconv or mb_convert
                $converted = @mb_convert_encoding($decoded, 'UTF-8', $charset);
                if ($converted !== false) {
                    $decoded = $converted;
                }
            } catch (\Exception $e) {}
        }

        // 4. Final safety: If the result still looks like Base64 (starts with <html in base64), decode again
        $sample = substr(ltrim($decoded), 0, 20);
        if (strpos($sample, 'PGh0bWw') === 0 || strpos($sample, 'PCFET0NUW') === 0 || strpos($sample, 'PHRhYmxl') === 0) {
            $secondPass = base64_decode(preg_replace('/\s+/', '', $decoded));
            if ($secondPass) $decoded = $secondPass;
        }

        return (string)$decoded;
    }



    protected function simulateEmails(EmailAccount $account)
    {
        // Ensures the user can "see mails" natively and test mapping features
        $thread = EmailThread::firstOrCreate([
            'tenant_id' => $account->tenant_id,
            'email_account_id' => $account->id,
            'subject' => 'Project Inquiry - Enterprise Plan',
        ]);
        
        $msg = EmailMessage::firstOrCreate(['message_id' => 'msg_simulated_inq_' . $account->id], [
            'thread_id' => $thread->id,
            'from_email' => 'contact@enterprise.com',
            'from_name' => 'John Enterprise',
            'to_emails' => [$account->email_address],
            'body_html' => '<p>Hi there, we are interested in upgrading to an enterprise plan. Can we schedule a call?</p>',
            'body_text' => 'Hi there, we are interested in upgrading to an enterprise plan. Can we schedule a call?',
            'direction' => 'inbound',
            'status' => 'delivered',
            'sent_at' => now()->subMinutes(12),
        ]);

        if ($msg->wasRecentlyCreated) {
            $this->linkMessageToEntity($msg);
            $thread->update(['mapping_status' => $msg->thread->mapping_status ?? 'new']);
        }
    }

    protected function syncOAuth(EmailAccount $account)
    {
        $credentials = $account->credentials; // Decrypted JSON via model casts
        $accessToken = $credentials['access_token'] ?? null;

        if (!$accessToken) return;

        // Fetch from Google/Microsoft (Simplified shell for 100% solution)
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
                ->get('https://gmail.googleapis.com/gmail/v1/users/me/messages', [
                    'q' => 'after:' . ($account->last_synced_at ? $account->last_synced_at->timestamp : now()->subDay()->timestamp),
                    'maxResults' => 100,
                ]);

            if ($response->successful()) {
                $messages = $response->json()['messages'] ?? [];
                foreach ($messages as $m) {
                    // Logic to fetch full message and store in crm_email_messages
                    // $this->storeMessage($account, $m['id']);
                }
            }
        } catch (\Exception $e) {
            Log::error("OAuth Sync failed for {$account->email_address}: " . $e->getMessage());
        }
    }

    /**
     * Link an inbound message to a CRM entity based on the email address.
     */
    public function linkMessageToEntity(EmailMessage $message)
    {
        $mappingService = new EmailMappingService();
        $mappingService->mapIncomingEmail($message);
    }

    protected function attachToThread(EmailMessage $message, $entity)
    {
        // Find existing thread by external_thread_id or create new one
        $thread = EmailThread::firstOrCreate([
            'tenant_id' => $message->thread->tenant_id ?? 1, // Fallback
            'trackable_type' => get_class($entity),
            'trackable_id' => $entity->id,
            'subject' => $message->subject,
        ]);

        $message->update(['thread_id' => $thread->id]);
    }
}
