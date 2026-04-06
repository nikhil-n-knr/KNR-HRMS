<?php

namespace App\Jobs\CRM;

use App\Models\CRM\Contact;
use App\Models\CRM\ContactImport;
use App\Notifications\CRM\ContactImportCompleted;
use App\Services\Communication\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use SplFileObject;
use Illuminate\Support\Facades\Log;

class ProcessContactImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $import;

    /**
     * Create a new job instance.
     */
    public function __construct(ContactImport $import)
    {
        $this->import = $import;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $this->import->update(['status' => 'processing']);

        $filePath = Storage::path($this->import->file_path);
        
        if (!file_exists($filePath)) {
            $this->import->update(['status' => 'failed', 'errors' => ['file' => 'File not found on server']]);
            $notificationService->send($this->import->user, new ContactImportCompleted($this->import));
            return;
        }

        $processed = 0;
        $failed = 0;
        $errors = [];
        $rowNumber = 0;
        
        try {
            $file = new SplFileObject($filePath);
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
            
            // Read Header
            $header = $file->fgetcsv();
            $headerMap = array_flip(array_map('strtolower', $header)); // Convert to lower case map

            // Determine column indexes
            $emailIdx = $headerMap['email'] ?? -1;
            $firstNameIdx = $headerMap['first_name'] ?? $headerMap['name'] ?? -1;
            $lastNameIdx = $headerMap['last_name'] ?? -1;
            $phoneIdx = $headerMap['phone'] ?? -1;

            if ($emailIdx === -1) {
                $this->import->update(['status' => 'failed', 'errors' => ['header' => 'Missing "email" column in CSV']]);
                return;
            }

            foreach ($file as $row) {
                $rowNumber++;
                if (empty($row) || count($row) < count($header)) continue;

                try {
                    $email = $row[$emailIdx] ?? null;

                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        Contact::updateOrCreate(
                            [
                                'tenant_id' => $this->import->tenant_id,
                                'email' => $email
                            ],
                            [
                                'first_name' => $firstNameIdx !== -1 ? ($row[$firstNameIdx] ?? '') : '',
                                'last_name' => $lastNameIdx !== -1 ? ($row[$lastNameIdx] ?? '') : '',
                                'phone' => $phoneIdx !== -1 ? ($row[$phoneIdx] ?? '') : null,
                                'created_by' => $this->import->imported_by
                            ]
                        );
                        $processed++;
                    } else {
                        $failed++;
                        $errors[] = "Row {$rowNumber}: Invalid Email '{$email}'";
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
                
                // Keep errors log manageable
                if (count($errors) > 100) break;
            }

            // Update Import Record
            $this->import->update([
                'status' => 'completed',
                'processed_records' => $processed,
                'failed_records' => $failed,
                'errors' => $errors,
                'total_records' => $processed + $failed
            ]);

            // Notify User
            $user = \App\Models\User::find($this->import->imported_by);
            if ($user) {
                $notificationService->send($user, new ContactImportCompleted($this->import));
            }

        } catch (\Exception $e) {
            Log::error("Import Failed: " . $e->getMessage());
            $this->import->update(['status' => 'failed', 'errors' => ['system' => $e->getMessage()]]);
        }
    }
}
