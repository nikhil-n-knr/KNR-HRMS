<?php

namespace App\Services\CRM;

use App\Models\CRM\Contact;
use App\Models\CRM\EmailMessage;
use App\Models\CRM\EmailThread;

class EmailMappingService
{
    /**
     * Map an incoming email message to a Contact.
     * 
     * @param EmailMessage $message
     * @return Contact|null
     */
    public function mapIncomingEmail(EmailMessage $message)
    {
        $thread = $message->thread;
        $tenantId = $thread->tenant_id;
        $senderEmail = $message->from_email;
        $senderName = $message->from_name;

        // 1. Exact Match
        $contact = Contact::where('tenant_id', $tenantId)
            ->where(function($q) use ($senderEmail) {
                $q->where('email', $senderEmail)
                  ->orWhere('secondary_email', $senderEmail);
            })->first();

        if ($contact) {
            $this->linkThread($thread, $contact, 'matched');
            return $contact;
        }

        // 2. Fuzzy Match (Domain based, ignoring common providers)
        $domain = substr(strrchr($senderEmail, "@"), 1);
        $commonProviders = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com'];
        
        if (!in_array(strtolower($domain), $commonProviders)) {
            $fuzzyContact = Contact::where('tenant_id', $tenantId)
                ->where('email', 'like', "%@{$domain}")
                ->first();
            
            if ($fuzzyContact) {
                // Link but mark as pending for review
                // A better approach is to create a new contact with the same account
                $contact = Contact::create([
                    'tenant_id' => $tenantId,
                    'account_id' => $fuzzyContact->account_id,
                    'first_name' => $this->extractFirstName($senderName, $senderEmail),
                    'last_name' => $this->extractLastName($senderName),
                    'email' => $senderEmail,
                    'status' => 'active',
                    'created_by' => 1, // System
                ]);

                $this->linkThread($thread, $contact, 'pending');
                return $contact;
            }
        }

        // 3. Fallback: Auto-create Stub Contact
        $contact = Contact::create([
            'tenant_id' => $tenantId,
            'first_name' => $this->extractFirstName($senderName, $senderEmail),
            'last_name' => $this->extractLastName($senderName),
            'email' => $senderEmail,
            'status' => 'active',
            'created_by' => 1, // System
        ]);

        $this->linkThread($thread, $contact, 'new');
        return $contact;
    }

    protected function linkThread(EmailThread $thread, Contact $contact, string $status)
    {
        $thread->update([
            'trackable_type' => Contact::class,
            'trackable_id' => $contact->id,
            'mapping_status' => $status,
        ]);
    }

    protected function extractFirstName(?string $name, string $email)
    {
        if ($name) {
            $parts = explode(' ', trim($name));
            return $parts[0];
        }
        
        // Extract from email (e.g., john.doe@... -> john)
        $prefix = explode('@', $email)[0];
        $prefixParts = explode('.', $prefix);
        return ucfirst($prefixParts[0]);
    }

    protected function extractLastName(?string $name)
    {
        if ($name) {
            $parts = explode(' ', trim($name));
            if (count($parts) > 1) {
                array_shift($parts);
                return implode(' ', $parts);
            }
        }
        return 'Unknown';
    }
}
