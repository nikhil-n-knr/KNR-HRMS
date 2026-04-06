<?php
use App\Models\CRM\EmailAccount;
use App\Models\CRM\EmailMessage;
use App\Models\CRM\EmailThread;
use App\Services\CRM\EmailSenderService;

echo "Attempting port 587/TLS...\n";
Config::set('mail.mailers.dynamic_test', [
    'transport' => 'smtp',
    'host' => 'mail.knrint.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'mail_testing@knrint.com',
    'password' => 'Mail@2026',
    'stream' => [
        'ssl' => [
            'allow_self_signed' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ],
]);
Mail::purge('dynamic_test');

$account = EmailAccount::where('email_address', 'mail_testing@knrint.com')->first();
if (!$account) {
    die("Account not found\n");
}

$thread = EmailThread::first();
if (!$thread) {
    // create a dummy thread if needed
    $thread = EmailThread::create([
        'tenant_id' => 1,
        'subject' => 'Tinker Test Subject',
        'email_account_id' => $account->id
    ]);
}

$msg = new EmailMessage();
$msg->to_emails = ['nikhil.infotec@gmail.com'];
$msg->body_html = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0; border-radius: 10px;">
        <h2 style="color: #4f46e5;">Status Update: CRM Communication System</h2>
        <p>Hello Nikhil,</p>
        <p>This is a synchronized test message from the HRMS Communication Hub to verify the final STMP handshaking protocols on port 587.</p>
        <p>If you receive this, it confirms the channel is open and the delivery route is functional from the local node.</p>
        <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0; font-size: 13px; color: #6b7280;">Test ID: ' . uniqid('crm_') . '</p>
            <p style="margin: 0; font-size: 13px; color: #6b7280;">Node: Local Development Hub</p>
        </div>
        <p>Best Regards,<br><strong>CRM Automation System</strong></p>
    </div>';
$msg->setRelation('thread', $thread);

$result = Mail::mailer('dynamic_test')->html($msg->body_html, function($m) use ($msg, $account) {
    $m->from($account->email_address, 'HRMS Test')
      ->to($msg->to_emails)
      ->subject('Final Port 587 Debug Test');
});

if ($result) {
    echo "SUCCESS: Mail handed to SMTP server.\n";
} else {
    echo "FAILED: Check laravel.log for details.\n";
}
