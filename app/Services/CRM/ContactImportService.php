<?php

namespace App\Services\CRM;

use App\Models\CRM\Contact;
use App\Models\CRM\Account;
use App\Models\CRM\ContactImport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ContactImportService
{
    protected $matchingService;

    public function __construct(ContactMatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Process a contact import file
     */
    public function process(ContactImport $import)
    {
        $import->update(['status' => 'processing']);
        
        $path = Storage::disk('local')->path($import->file_path);
        if (!file_exists($path)) {
            $import->update(['status' => 'failed', 'error_message' => 'File not found']);
            return;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        
        $successCount = 0;
        $failureCount = 0;
        $duplicateCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $data = array_combine($header, $row);
                
                // 1. Check for duplicates
                $isDuplicate = Contact::where('tenant_id', $import->tenant_id)
                    ->where(function($q) use ($data) {
                        $q->where('email', $data['email'])
                          ->orWhere('mobile', $data['mobile'] ?? null);
                    })->exists();

                if ($isDuplicate) {
                    $duplicateCount++;
                    continue;
                }

                // 2. Handle Account
                $accountId = null;
                if (!empty($data['account_name'])) {
                    $account = Account::firstOrCreate(
                        ['tenant_id' => $import->tenant_id, 'name' => $data['account_name']],
                        [
                            'industry' => $data['industry'] ?? null,
                            'website' => $data['website'] ?? null,
                            'created_by' => $import->imported_by
                        ]
                    );
                    $accountId = $account->id;
                }

                // 3. Determine if this is a Lead or Contact
                $isLead = !empty($data['company']) || !empty($data['source']);

                if ($isLead) {
                    \App\Models\CRM\Lead::create([
                        'tenant_id' => $import->tenant_id,
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? null,
                        'company' => $data['company'] ?? null,
                        'source' => $data['source'] ?? 'other',
                        'status' => 'new',
                        'created_by' => $import->imported_by,
                    ]);
                } else {
                    Contact::create([
                        'tenant_id' => $import->tenant_id,
                        'account_id' => $accountId,
                        'created_by' => $import->imported_by,
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? null,
                        'mobile' => $data['mobile'] ?? null,
                        'title' => $data['title'] ?? null,
                        'whatsapp' => $data['whatsapp'] ?? null,
                        'linkedin' => $data['linkedin'] ?? null,
                        'facebook' => $data['facebook'] ?? null,
                    ]);
                }

                $successCount++;

            } catch (\Exception $e) {
                $failureCount++;
                $errors[] = "Row " . ($successCount + $failureCount + $duplicateCount) . ": " . $e->getMessage();
            }
        }

        fclose($handle);

        $import->update([
            'status' => 'completed',
            'total_rows' => $successCount + $failureCount + $duplicateCount,
            'processed_rows' => $successCount,
            'failure_count' => $failureCount,
            'duplicate_count' => $duplicateCount,
            'errors' => $errors
        ]);
    }
}
