<?php
// Temporary IMAP Debug Script — DELETE AFTER USE
// Access at: http://localhost/HRMS/public/imap_test.php?key=debug123

if (($_GET['key'] ?? '') !== 'debug123') { die('Access denied'); }

echo "<pre>";
echo "PHP IMAP Extension: " . (function_exists('imap_open') ? '<b style="color:green">ENABLED</b>' : '<b style="color:red">DISABLED</b>') . "\n\n";

// Pull credentials from DB
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$account = \App\Models\CRM\EmailAccount::latest()->first();

if (!$account) {
    echo "❌ No EmailAccount found in database.\n";
    die('</pre>');
}

$creds = $account->credentials;
echo "Account: " . $account->email_address . "\n";
echo "Provider: " . $account->provider . "\n";
echo "Credentials keys: " . implode(', ', array_keys($creds ?? [])) . "\n";

$host     = $creds['host'] ?? '';
$port     = (int)($creds['port'] ?? 993);
$password = $creds['password'] ?? '';
$email    = $account->email_address;

echo "\nHost: $host\n";
echo "Port: $port\n";
echo "Email: $email\n";
echo "Password: " . (empty($password) ? '(EMPTY!)' : str_repeat('*', strlen($password))) . "\n\n";

if (!function_exists('imap_open')) {
    echo "❌ imap_open not available. Restart Apache after enabling extension=imap in php.ini\n";
    die('</pre>');
}

// Try different connection strings
$protocol   = ($port === 995 || $port === 110) ? 'pop3' : 'imap';
$encryption = ($port === 995 || $port === 993) ? 'ssl' : 'notls';

$attempts = [
    "{" . $host . ":" . $port . "/" . $protocol . "/" . $encryption . "/novalidate-cert}INBOX",
    "{" . $host . ":" . $port . "/" . $protocol . "/" . $encryption . "}INBOX",
    "{" . $host . ":" . $port . "/" . $protocol . "/novalidate-cert}INBOX",
    "{" . $host . "}INBOX",
];

foreach ($attempts as $mailbox) {
    echo "Trying: $mailbox ... ";
    imap_errors(); // Clear
    $conn = @imap_open($mailbox, $email, $password, 0, 1, ['DISABLE_AUTHENTICATOR' => 'GSSAPI']);
    if ($conn) {
        echo "<b style='color:green'>✅ CONNECTED!</b>\n";
        $check = imap_check($conn);
        echo "Messages in mailbox: " . $check->Nmsgs . "\n";
        $msgs = @imap_search($conn, 'ALL', SE_UID);
        echo "Total UIDs: " . (is_array($msgs) ? count($msgs) : 0) . "\n";
        if ($msgs) {
            $recent = array_slice(array_reverse($msgs), 0, 3);
            foreach ($recent as $uid) {
                $ov = @imap_fetch_overview($conn, $uid, FT_UID);
                $o = $ov[0] ?? null;
                if ($o) echo "  - [UID $uid] From: " . ($o->from ?? '?') . " | Subject: " . ($o->subject ?? '?') . "\n";
            }
        }
        imap_close($conn);
        break;
    } else {
        $errors = imap_errors();
        echo "<b style='color:red'>❌ FAILED</b> — " . implode(', ', $errors ?: ['Unknown error']) . "\n";
    }
}

echo "\n</pre>";
