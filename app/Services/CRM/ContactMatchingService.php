<?php

namespace App\Services\CRM;

use App\Models\CRM\Contact;

class ContactMatchingService
{
    /**
     * Find potential duplicate contacts within a tenant.
     */
    public function findDuplicates(int $tenantId)
    {
        // Find contacts with duplicate emails
        $duplicateEmails = Contact::where('tenant_id', $tenantId)
            ->select('email')
            ->whereNotNull('email')
            ->groupBy('email')
            ->havingRaw('COUNT(id) > 1')
            ->pluck('email');

        $byEmail = Contact::where('tenant_id', $tenantId)
            ->whereIn('email', $duplicateEmails)
            ->get()
            ->groupBy('email');

        // Find contacts with duplicate phone numbers
        $duplicatePhones = Contact::where('tenant_id', $tenantId)
            ->select('phone')
            ->whereNotNull('phone')
            ->groupBy('phone')
            ->havingRaw('COUNT(id) > 1')
            ->pluck('phone');

        $byPhone = Contact::where('tenant_id', $tenantId)
            ->whereIn('phone', $duplicatePhones)
            ->get()
            ->groupBy('phone');

        return [
            'by_email' => $byEmail,
            'by_phone' => $byPhone,
            'total_duplicates' => $duplicateEmails->count() + $duplicatePhones->count()
        ];
    }
}
