<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompOffCredit;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;

class CompOffController extends Controller
{
    public function index()
    {
        return response()->json([
            'credits' => CompOffCredit::with('employee')->latest()->get()
        ]);
    }

    // Logic to scan for past weekend work and grant credits
    public function scan(Request $request) 
    {
        $startDate = Carbon::parse($request->input('start_date', now()->subMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()));

        $logs = AttendanceLog::with('employee')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $count = 0;

        foreach ($logs as $log) {
            $date = Carbon::parse($log->date);
            
            // Check if Weekend (Sat/Sun) - Logic can be refined for specific shifts
            $isWeekend = $date->isWeekend();
            
            // In a real app, also check Holiday Calendar here
            if (!$isWeekend) continue;

            // Check if worked enough (e.g. > 4 hours)
            // Assuming log has 'minutes' or we calculate from sessions. 
            // For now, assuming AttendanceLog calculation logic exists or we use raw check
            // Let's assume we have a helper or minutes column.
            // If not, we might need to rely on sessions. 
            // For this phase, let's assume 'status' == 'Present' on a weekend is enough or add a dummy check.
            
            if ($log->status !== 'Present') continue;

            // Check if credit already exists
            $exists = CompOffCredit::where('employee_id', $log->employee_id)
                ->where('date_earned', $log->date)
                ->exists();

            if (!$exists) {
                CompOffCredit::create([
                    'employee_id' => $log->employee_id,
                    'date_earned' => $log->date,
                    'minutes_earned' => 480, // Full day
                    'expiry_date' => $date->copy()->addDays(90),
                    'status' => 'Available',
                    'notes' => 'Auto-credited for weekend work'
                ]);
                $count++;
            }
        }

        return response()->json(['message' => "Scanned period. Granted $count new comp-off credits."]);
    }
}
