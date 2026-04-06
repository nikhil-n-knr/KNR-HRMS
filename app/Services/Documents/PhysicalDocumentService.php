<?php

namespace App\Services\Documents;

use App\Models\PhysicalRecord;
use App\Models\PhysicalDocumentLocation;
use Exception;

class PhysicalDocumentService
{
    /**
     * Check-in a physical document (e.g., HR filing a passport).
     */
    public function checkIn(?int $userId, string $docType, int $locationId, string $ref, int $receivedBy, ?string $outsiderName = null): PhysicalRecord
    {
        // verify location exists
        $location = PhysicalDocumentLocation::findOrFail($locationId);

        return PhysicalRecord::create([
            'user_id' => $userId,
            'outsider_name' => $outsiderName, // Added
            'document_type' => $docType,
            'location_id' => $locationId,
            'container_ref' => $ref,
            'status' => 'In_Custody',
            'received_by' => $receivedBy,
            'received_at' => now()
        ]);
    }

    /**
     * Checkout document (e.g., Employee borrowing it for Visa interview).
     */
    public function checkout(int $recordId, int $authorizedBy): PhysicalRecord
    {
        $record = PhysicalRecord::findOrFail($recordId);

        if ($record->status !== 'In_Custody') {
            throw new Exception("Document is not in custody. Status: {$record->status}");
        }

        $record->update([
            'status' => 'With_Employee',
            'notes' => $record->notes . "\nChecked out by AuthID: {$authorizedBy} at " . now()
        ]);

        return $record;
    }

    /**
     * Mark as Missing (Audit failure).
     */
    public function markMissing(int $recordId): PhysicalRecord
    {
        $record = PhysicalRecord::findOrFail($recordId);
        $record->update(['status' => 'Missing']);
        return $record;
    }
}
