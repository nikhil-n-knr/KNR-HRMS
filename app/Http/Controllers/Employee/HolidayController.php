<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * List upcoming holidays
     */
    public function index(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;

        $all = $this->getVisibleHolidays($year);

        $holidays = $all->groupBy(function($val) {
            return Carbon::parse($val->date)->format('F'); // Group by Month Name
        });

        $counts = [
             'total' => $all->count(),
             'fixed' => $all->where('type', 'Fixed')->count(),
             'restricted' => $all->where('type', 'Restricted')->count()
        ];

        return response()->json([
            'holidays' => $holidays,
            'year' => $year,
            'years' => Holiday::selectRaw('YEAR(date) as year')->distinct()->orderBy('year', 'desc')->pluck('year'),
            'counts' => $counts
        ]);
    }

    public function export(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        
        $holidays = $this->getVisibleHolidays($year);
        
        $filename = 'holiday_calendar_' . $year . '.csv';

        return response()->streamDownload(function() use ($holidays) {
             $handle = fopen('php://output', 'w');
             fputcsv($handle, ['Holiday Name', 'Date', 'Day', 'Type']);
             
             foreach ($holidays as $row) {
                 fputcsv($handle, [
                     $row->name,
                     $row->date->format('Y-m-d'),
                     $row->date->format('l'),
                     $row->type
                 ]);
             }
             fclose($handle);
        }, $filename);
    }

    private function getVisibleHolidays($year) {
        // Check Published Status
        // Assuming Tenant Scoping logic matches Admin's (Auth User -> Tenant)
        $tenantId = auth()->user()->tenant_id ?? null;
        $status = \App\Models\HolidayCalendarStatus::where('year', $year)
                  ->where('tenant_id', $tenantId)
                  ->first();
                  
        if (!$status || !$status->is_published) {
            return collect([]); // Not published yet
        }

        // Fetch Regular
        $regular = Holiday::whereYear('date', $year)->where('is_recurring', false)->get();
        
        // Fetch Recurring
        $recurring = Holiday::where('is_recurring', true)->get();
        
        // Fetch Exceptions
        $exceptions = \App\Models\HolidayException::where('year', $year)->get()->keyBy('holiday_id');
        
        $recurringProjected = $recurring->map(function($h) use ($year, $exceptions) {
             if (isset($exceptions[$h->id]) && $exceptions[$h->id]->is_hidden) return null;
             // Determine date for current year
             try {
                $h->date = $h->date->setYear($year);
             } catch (\Exception $e) {}
             return $h;
        })->filter();
        
        return $regular->merge($recurringProjected)->sortBy('date');
    }
}
