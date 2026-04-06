<?php

namespace App\Services\CRM;

use App\Models\CRM\Contact;
use App\Models\CRM\ContactRelationship;

class RelationshipMapper
{
    /**
     * Map a relationship between two contacts.
     */
    public function mapRelationship($contactId, $relatedContactId, $type, $strength = 5, $notes = null)
    {
        // Ensure bidirectional or standardized mapping
        return ContactRelationship::updateOrCreate(
            [
                'tenant_id' => auth()->user()->tenant_id,
                'contact_id' => $contactId,
                'related_contact_id' => $relatedContactId
            ],
            [
                'relation_type' => $type,
                'strength' => $strength,
                'notes' => $notes
            ]
        );
    }

    /**
     * Get the connection graph data for a contact.
     */
    public function getGraphData(Contact $contact)
    {
        $relationships = $contact->relationships()->with('relatedContact:id,first_name,last_name,title')->get();
        
        $nodes = [
            [
                'id' => $contact->id,
                'label' => $contact->full_name,
                'title' => $contact->title,
                'group' => 'main'
            ]
        ];
        
        $links = [];
        
        foreach ($relationships as $rel) {
            $nodes[] = [
                'id' => $rel->related_contact_id,
                'label' => $rel->relatedContact->full_name,
                'title' => $rel->relatedContact->title,
                'group' => 'connected'
            ];
            
            $links[] = [
                'source' => $contact->id,
                'target' => $rel->related_contact_id,
                'label' => $rel->relation_type,
                'strength' => $rel->strength
            ];
        }
        
        return [
            'nodes' => $nodes,
            'links' => $links
        ];
    }
}
