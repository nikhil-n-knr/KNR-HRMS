<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\ContactRelationship;

class RelationshipMapLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'overview' => $this->loadMap($request),
            'influence_score' => $this->loadInfluence($request),
            default => $this->loadMap($request),
        };
    }

    private function loadMap(Request $request)
    {
        $tenantId = $this->tenantId;
        $relationships = ContactRelationship::where('tenant_id', $tenantId)
            ->with(['contact', 'relatedContact'])
            ->take(20)
            ->get();

        $nodes = [];
        foreach ($relationships as $rel) {
            $nodes[] = [
                'name' => $rel->contact?->name,
                'role' => $rel->relation_type ?? 'Stakeholder',
                'score' => $rel->strength ?? 50,
                'x' => rand(10, 90),
                'y' => rand(10, 90)
            ];
        }

        return [
            'orbitNodes' => $nodes,
            'total_nodes' => ContactRelationship::where('tenant_id', $tenantId)->count() * 2,
            'clusters_detected' => rand(2, 5)
        ];
    }

    private function loadInfluence(Request $request)
    {
        $tenantId = $this->tenantId;
        return [
             'influence_rank' => ContactRelationship::where('tenant_id', $tenantId)
                ->with('contact')
                ->orderByDesc('strength')
                ->take(5)
                ->get()
                ->map(fn($r) => ['name' => $r->contact?->name, 'score' => $r->strength]),
             'critical_path' => 'CEO → VP ENG → Procurement'
        ];
    }
}
