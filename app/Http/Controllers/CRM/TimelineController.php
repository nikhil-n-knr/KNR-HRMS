<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Services\CRM\TimelineService;
use App\Models\CRM\Contact;
use App\Models\CRM\Lead;
use App\Models\CRM\Deal;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    protected $timelineService;

    public function __construct(TimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
    }

    /**
     * Get the timeline for a specific entity.
     */
    public function show(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:contact,lead,deal',
            'id' => 'required|integer',
        ]);

        $entity = null;
        switch ($request->type) {
            case 'contact':
                $entity = Contact::findOrFail($request->id);
                break;
            case 'lead':
                $entity = Lead::findOrFail($request->id);
                break;
            case 'deal':
                $entity = Deal::findOrFail($request->id);
                break;
        }

        return response()->json([
            'events' => $this->timelineService->getTimeline($entity),
        ]);
    }
}
