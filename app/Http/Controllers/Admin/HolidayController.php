<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\HolidayException;
use App\Services\Infrastructure\LoggerService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class HolidayController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        
        $query = Holiday::query();
        
        if (auth()->check()) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Logic: (Specific Year AND Non-Recurring) OR (Recurring)
        $query->where(function($q) use ($year) {
             $q->where(function($sub) use ($year) {
                 $sub->whereYear('date', $year)->where('is_recurring', false);
             })->orWhere('is_recurring', true);
        });

        // Get all to process exceptions locally (Pagination after processing)
        // Holidays are small dataset, typically 10-20 per year. Fetching all is fine.
        $allHolidays = $query->get();
        
        // Fetch Exceptions for this year
        $exceptions = HolidayException::where('year', $year)
                        ->whereIn('holiday_id', $allHolidays->pluck('id'))
                        ->get()
                        ->keyBy('holiday_id');

        $processed = $allHolidays->map(function($h) use ($year, $exceptions) {
             if ($h->is_recurring) {
                 // Project date to selected year
                 // Keep original day/month. 
                 // Note: If leap year 29 Feb -> non-leap year? Carbon handles it (usually 1st March).
                 try {
                    $h->date = $h->date->setYear($year);
                 } catch (\Exception $e) {
                    // Fallback for leap year edge cases if needed
                 }
                 
                 if (isset($exceptions[$h->id])) {
                     $h->is_hidden_for_year = $exceptions[$h->id]->is_hidden;
                 }
             }
             return $h;
        })->sortBy('date')->values();

        // Manual Pagination
        $perPage = $request->per_page ?? 15;
        $page = $request->page ?? 1;
        $total = $processed->count();
        $items = $processed->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items, $total, $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]
        );

        // Check Publish Status
        $status = \App\Models\HolidayCalendarStatus::where('year', $year)
                  ->where('tenant_id', auth()->user()->tenant_id ?? null)
                  ->first();

        $counts = [
            'total' => $processed->count(),
            'fixed' => $processed->where('type', 'Fixed')->count(),
            'restricted' => $processed->where('type', 'Restricted')->count()
        ];

        return response()->json([
            'data' => $paginator,
            'years' => Holiday::selectRaw('YEAR(date) as year')->distinct()->orderBy('year', 'desc')->pluck('year'),
            'is_published' => $status ? $status->is_published : false,
            'counts' => $counts
        ]);
    }

    public function publish(Request $request) 
    {
        $year = $request->year ?? Carbon::now()->year;
        
        \App\Models\HolidayCalendarStatus::updateOrCreate(
            ['tenant_id' => auth()->user()->tenant_id ?? null, 'year' => $year],
            [
                'is_published' => true,
                'published_at' => now(),
                'published_by' => auth()->id()
            ]
        );
        
        $this->logger->log('leave_management', 'publish', "Published Holiday Calendar for {$year}");
        
        return response()->json(['message' => "Holiday Calendar for $year has been published."]);
    }

    public function unpublish(Request $request) 
    {
        $year = $request->year ?? Carbon::now()->year;
        
        \App\Models\HolidayCalendarStatus::updateOrCreate(
            ['tenant_id' => auth()->user()->tenant_id ?? null, 'year' => $year],
            [
                'is_published' => false,
                'published_at' => null,
                'published_by' => null
            ]
        );
        
        $this->logger->log('leave_management', 'unpublish', "Unpublished Holiday Calendar for {$year}");
        
        return response()->json(['message' => "Holiday Calendar for $year has been unpublished."]);
    }

    public function toggleVisibility(Request $request, Holiday $holiday)
    {
        $year = $request->year ?? Carbon::now()->year;
        
        // Find or Create Exception
        $exception = HolidayException::firstOrNew([
            'holiday_id' => $holiday->id,
            'year' => $year
        ]);
        
        // Toggle
        $exception->is_hidden = !$exception->is_hidden;
        $exception->save();
        
        return response()->json(['message' => 'Visibility updated', 'is_hidden' => $exception->is_hidden]);
    }

    public function export(Request $request)
    {
        $query = Holiday::query();
        if (auth()->check()) $query->where('tenant_id', auth()->user()->tenant_id);
        
        if ($request->filled('year')) $query->whereYear('date', $request->year);
        else $query->whereYear('date', Carbon::now()->year);

        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('search')) $query->where('name', 'like', "%{$request->search}%");
        
        $query->orderBy('date', 'asc');
        
        $filename = 'holidays_' . ($request->year ?? date('Y')) . '.csv';

        return response()->streamDownload(function() use ($query) {
             $handle = fopen('php://output', 'w');
             fputcsv($handle, ['Name', 'Date', 'Day', 'Type', 'Recurring']);
             
             $query->chunk(200, function($rows) use ($handle) {
                 foreach ($rows as $row) {
                     fputcsv($handle, [
                         $row->name,
                         $row->date->format('Y-m-d'),
                         $row->date->format('l'),
                         $row->type,
                         $row->is_recurring ? 'Yes' : 'No'
                     ]);
                 }
             });
             fclose($handle);
        }, $filename);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:Fixed,Restricted',
            'is_recurring' => 'boolean',
            'applies_to_locations' => 'nullable|array'
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $holiday = Holiday::create($validated);

        $this->logger->log('leave_management', 'create', "Created Holiday: {$holiday->name} ({$holiday->date->format('Y-m-d')})");

        return response()->json(['message' => 'Holiday created successfully', 'holiday' => $holiday], 201);
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:Fixed,Restricted',
            'is_recurring' => 'boolean',
            'applies_to_locations' => 'nullable|array'
        ]);

        $holiday->update($validated);

        $this->logger->log('leave_management', 'update', "Updated Holiday: {$holiday->name}");

        return response()->json(['message' => 'Holiday updated successfully', 'holiday' => $holiday]);
    }

    public function destroy(Holiday $holiday)
    {
        $name = $holiday->name;
        $holiday->delete();

        $this->logger->log('leave_management', 'delete', "Deleted Holiday: {$name}");

        return response()->json(['message' => 'Holiday deleted successfully']);
    }
}
