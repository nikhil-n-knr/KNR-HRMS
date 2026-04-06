<?php

namespace App\Notifications\CRM;

use App\Models\CRM\ContactImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactImportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public $import;

    /**
     * Create a new notification instance.
     */
    public function __construct(ContactImport $import)
    {
        $this->import = $import;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Default to database notification
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'contact_import_completed',
            'title' => 'Import Processed',
            'message' => "Contact import finished. Processed: {$this->import->processed_records}, Failed: {$this->import->failed_records}.",
            'import_id' => $this->import->id,
            'url' => route('contacts.imports.index'), // Assuming route name exists
            'status' => $this->import->status,
        ];
    }
}
