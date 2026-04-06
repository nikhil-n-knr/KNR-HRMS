<?php

namespace App\Services\CRM;

use App\Models\CRM\EmailAccount;
use App\Models\CRM\EmailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailSenderService
{
    /**
     * Send an outbound message using the account's SMTP credentials.
     */
    public function send(EmailAccount $account, EmailMessage $message): string
    {
        $credentials = $account->credentials;
        
        $host = $credentials['smtp_host'] ?? $credentials['host'] ?? '';
        $port = $credentials['smtp_port'] ?? 465;
        $username = $account->email_address;
        $password = $credentials['password'] ?? '';

        if (empty($host) || empty($password)) {
            return "Missing SMTP credentials. Since the Security Key (APP_KEY) was changed, you MUST re-enter your email password in Sync Settings.";
        }

        $encryption = ($port == 465) ? 'ssl' : (($port == 587) ? 'tls' : null);

        // Configure a temporary mailer
        $config = [
            'transport' => 'smtp',
            'host' => $host,
            'port' => $port,
            'encryption' => $encryption,
            'username' => $username,
            'password' => $password,
            'timeout' => 15,
            'auth_mode' => null,
            'stream' => [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ],
        ];

        Config::set('mail.mailers.dynamic_smtp', $config);
        Mail::purge('dynamic_smtp');

        try {
            Log::info("Attempting to send email via dynamic SMTP", [
                'from' => $account->email_address,
                'to' => $message->to_emails,
                'subject' => $message->thread->subject
            ]);

            Mail::mailer('dynamic_smtp')->html($message->body_html ?: $message->body_text, function ($m) use ($message, $account) {
                $m->from($account->email_address, auth()->user()->name ?? 'HRMS Office')
                  ->to($message->to_emails)
                  ->subject($message->thread->subject ?? 'No Subject');

                if (!empty($message->cc_emails)) {
                    $m->cc($message->cc_emails);
                }

                if (!empty($message->bcc_emails)) {
                    $m->bcc($message->bcc_emails);
                }
            });

            // OPTIONAL: Try to save a copy to the Sent folder if it's an IMAP account
            $this->saveToSentFolder($account, $message);

            Log::info("Email sent successfully via dynamic SMTP for {$account->email_address} to " . implode(', ', (array)$message->to_emails));
            return 'success';
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error("Failed to send email via SMTP for {$account->email_address} to " . implode(', ', (array)$message->to_emails) . ": " . $error, [
                'exception' => $e
            ]);
            return $error;
        }
    }

    /**
     * Helper to append a copy of the sent email to the IMAP Sent folder.
     */
    protected function saveToSentFolder(EmailAccount $account, EmailMessage $message)
    {
        if ($account->provider !== 'imap' || !function_exists('imap_open')) {
            return;
        }

        try {
            $credentials = $account->credentials;
            $host = $credentials['host'] ?? '';
            $port = (int) ($credentials['port'] ?? 993);
            $user = $account->email_address;
            $pass = $credentials['password'] ?? '';
            $protocol = $credentials['protocol'] ?? 'imap';
            $encryption = $port === 993 ? 'ssl' : 'notls';

            // Common Sent folder names
            $sentFolders = ['Sent', 'Sent Messages', 'Sent Items', 'INBOX.Sent'];
            
            $mailboxBase = '{' . $host . ':' . $port . '/' . $protocol . '/' . $encryption . '/novalidate-cert}';
            
            $imap = @imap_open($mailboxBase, $user, $pass);
            if (!$imap) return;

            // Try to find the sent folder
            $list = imap_list($imap, $mailboxBase, "*");
            $targetFolder = "Sent"; 
            if ($list) {
                foreach ($list as $f) {
                    foreach ($sentFolders as $s) {
                        if (stripos($f, $s) !== false) {
                            $targetFolder = str_replace($mailboxBase, '', $f);
                            break 2;
                        }
                    }
                }
            }

            $date = date("d-M-Y H:i:s O");
            $to = implode(', ', (array)$message->to_emails);
            $subject = $message->thread->subject ?? 'No Subject';
            
            $boundary = "----=_Part_" . md5(time());
            $msg  = "From: {$user}\r\n";
            $msg .= "To: {$to}\r\n";
            $msg .= "Subject: {$subject}\r\n";
            $msg .= "Date: {$date}\r\n";
            $msg .= "MIME-Version: 1.0\r\n";
            $msg .= "Content-Type: text/html; charset=utf-8\r\n";
            $msg .= "\r\n";
            $msg .= $message->body_html ?: $message->body_text;

            imap_append($imap, $mailboxBase . $targetFolder, $msg);
            imap_close($imap);
        } catch (\Exception $e) {
            Log::warning("Could not save copy to Sent folder: " . $e->getMessage());
        }
    }
}
