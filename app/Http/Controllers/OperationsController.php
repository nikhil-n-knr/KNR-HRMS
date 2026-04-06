<?php

namespace App\Http\Controllers;

use App\Services\Calendar\UnifiedCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperationsController extends Controller
{
    protected $calendarService;

    public function __construct(UnifiedCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function index()
    {
        return Inertia::render('Operations/Calendar', [
            // Pass any initial data if needed, like user filters
        ]);
    }

    public function events(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $start = Carbon::parse($request->input('start'))->startOfDay();
        $end = Carbon::parse($request->input('end'))->endOfDay();

        $events = $this->calendarService->fetchEvents($request->user(), $start, $end);

        // Transform Collection of CalendarEvent objects to array for JSON
        return response()->json(
            $events->map(fn($e) => $e->toArray())
        );
    }
}
