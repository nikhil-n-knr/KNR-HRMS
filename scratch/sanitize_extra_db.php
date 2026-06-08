<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    echo "Sanitizing extra references to KNR...\n";

    // 1. locations name
    $locs = DB::table('locations')->where('name', 'like', '%knr%')->get();
    foreach ($locs as $l) {
        $newName = str_ireplace('KNR', 'PiSparrow', $l->name);
        DB::table('locations')->where('id', $l->id)->update(['name' => $newName]);
        echo "Updated Location ID {$l->id}: {$l->name} -> {$newName}\n";
    }

    // 2. clients code
    $cls = DB::table('clients')->where('code', 'like', '%knr%')->get();
    foreach ($cls as $c) {
        $newCode = str_ireplace('KNR', 'PSP', $c->code);
        DB::table('clients')->where('id', $c->id)->update(['code' => $newCode]);
        echo "Updated Client Code ID {$c->id}: {$c->code} -> {$newCode}\n";
    }

    // 3. biometric_devices location_name
    $biodev = DB::table('biometric_devices')->where('location_name', 'like', '%knr%')->get();
    foreach ($biodev as $bd) {
        $newName = str_ireplace('KNR', 'PiSparrow', $bd->location_name);
        DB::table('biometric_devices')->where('id', $bd->id)->update(['location_name' => $newName]);
        echo "Updated Biometric Device ID {$bd->id}: {$bd->location_name} -> {$newName}\n";
    }

    // 4. crm_email_accounts email_address
    $accounts = DB::table('crm_email_accounts')->where('email_address', 'like', '%knr%')->get();
    foreach ($accounts as $acc) {
        $newEmail = str_ireplace('knrint.com', 'pisparrow.com', $acc->email_address);
        DB::table('crm_email_accounts')->where('id', $acc->id)->update(['email_address' => $newEmail]);
        echo "Updated Email Account ID {$acc->id}: {$acc->email_address} -> {$newEmail}\n";
    }

    // 5. crm_email_messages
    $messages = DB::table('crm_email_messages')->get();
    foreach ($messages as $msg) {
        $newFromEmail = str_ireplace('knrint.com', 'pisparrow.com', $msg->from_email);
        $newFromName = str_ireplace('KNR', 'PiSparrow', $msg->from_name);
        $newToEmails = str_ireplace('knrint.com', 'pisparrow.com', $msg->to_emails);
        $newBodyHtml = str_ireplace('knrint.com', 'pisparrow.com', $msg->body_html);
        $newBodyHtml = str_ireplace('KNR', 'PiSparrow', $newBodyHtml);
        $newBodyText = str_ireplace('knrint.com', 'pisparrow.com', $msg->body_text);
        $newBodyText = str_ireplace('KNR', 'PiSparrow', $newBodyText);

        DB::table('crm_email_messages')->where('id', $msg->id)->update([
            'from_email' => $newFromEmail,
            'from_name' => $newFromName,
            'to_emails' => $newToEmails,
            'body_html' => $newBodyHtml,
            'body_text' => $newBodyText
        ]);
    }
    echo "Updated crm_email_messages.\n";

    // 6. crm_email_threads subject
    $threads = DB::table('crm_email_threads')->where('subject', 'like', '%knr%')->get();
    foreach ($threads as $t) {
        $newSubj = str_ireplace('KNR', 'PiSparrow', $t->subject);
        DB::table('crm_email_threads')->where('id', $t->id)->update(['subject' => $newSubj]);
        echo "Updated Email Thread ID {$t->id} Subject.\n";
    }

    // 7. employee_document_storage_maps
    $docsMap = DB::table('employee_document_storage_maps')->get();
    foreach ($docsMap as $dm) {
        $newLocal = str_ireplace('knr', 'pisparrow', $dm->local_path);
        $newS3 = str_ireplace('knr', 'pisparrow', $dm->s3_url);
        DB::table('employee_document_storage_maps')->where('id', $dm->id)->update([
            'local_path' => $newLocal,
            's3_url' => $newS3
        ]);
    }
    echo "Updated employee_document_storage_maps.\n";

    // 8. employee_documents file_path
    $empDocs = DB::table('employee_documents')->get();
    foreach ($empDocs as $ed) {
        $newPath = str_ireplace('knr', 'pisparrow', $ed->file_path);
        DB::table('employee_documents')->where('id', $ed->id)->update(['file_path' => $newPath]);
    }
    echo "Updated employee_documents.\n";

    // 9. notifications data
    $notifications = DB::table('notifications')->get();
    foreach ($notifications as $n) {
        $newData = str_ireplace('knrint.com', 'pisparrow.com', $n->data);
        $newData = str_ireplace('KNR', 'PiSparrow', $newData);
        DB::table('notifications')->where('id', $n->id)->update(['data' => $newData]);
    }
    echo "Updated notifications data.\n";

    // 10. activity_logs properties
    $logs = DB::table('activity_logs')->get();
    foreach ($logs as $l) {
        $newProps = str_ireplace('knrint.com', 'pisparrow.com', $l->properties);
        $newProps = str_ireplace('KNR', 'PiSparrow', $newProps);
        DB::table('activity_logs')->where('id', $l->id)->update(['properties' => $newProps]);
    }
    echo "Updated activity_logs properties.\n";

    // 11. cms_page_views (optionally clear or sanitize since it has knr domains or URLs)
    DB::table('cms_page_views')->truncate();
    echo "Truncated cms_page_views.\n";

    DB::commit();
    echo "\nExtra brand sanitization completed successfully!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
