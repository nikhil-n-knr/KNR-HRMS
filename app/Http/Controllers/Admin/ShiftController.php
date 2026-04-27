<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Services\Infrastructure\LoggerService;

class ShiftController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request)
    {
        $shifts = Shift::orderBy('start_time')->get();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json($shifts);
        }

        return Inertia::render('Admin/Attendance/Hub', [
            'tab' => 'policies', 
            'subTab' => 'shifts',
            'shifts' => $shifts,
            'locations' => \App\Models\Location::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:shifts,code',
            'start_time' => 'required|date_format:H:i:s,H:i',
            'end_time' => 'required|date_format:H:i:s,H:i|different:start_time',
            'work_days' => 'required|array|min:1',
            'work_days.*' => 'string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'week_off_rules' => 'nullable|array',
            'week_off_rules.*.weekday' => 'required_with:week_off_rules|string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'week_off_rules.*.weeks' => 'required_with:week_off_rules|array|min:1',
            'week_off_rules.*.weeks.*' => 'string|in:1,2,3,4,last',
            'grace_late_entry' => 'nullable|integer|min:0',
            'grace_early_exit' => 'nullable|integer|min:0',
            'post_shift_auto_checkout_cap_minutes' => 'nullable|integer|min:0|max:720',
            'color' => 'nullable|string|max:7', // Hex code
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'is_default' => 'boolean',
            'break_policy' => 'nullable|array', // JSON
            'ip_restrictions' => 'nullable|array' // JSON
        ]);

        try {
            DB::beginTransaction();

            $validated['week_off_rules'] = $this->normalizeWeekOffRules($validated['week_off_rules'] ?? []);

            if ($request->boolean('is_default') && empty($request->location_ids)) {
                Shift::whereNull('location_ids')->update(['is_default' => false]);
            }

            $validated['tenant_id'] = 1; 
            
            $shift = Shift::create($validated);

            $this->logger->log('attendance', 'shift_create', "Created Shift: {$shift->name}", ['user_id' => auth()->id()]);

            DB::commit();

            return redirect()->back()->with('success', 'Shift created successfully.')
                ->setStatusCode(303);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create shift: ' . $e->getMessage())
                ->setStatusCode(303);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:shifts,code,' . $shift->id,
            'start_time' => 'required|date_format:H:i:s,H:i',
            'end_time' => 'required|date_format:H:i:s,H:i|different:start_time',
            'work_days' => 'required|array|min:1',
            'work_days.*' => 'string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'week_off_rules' => 'nullable|array',
            'week_off_rules.*.weekday' => 'required_with:week_off_rules|string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'week_off_rules.*.weeks' => 'required_with:week_off_rules|array|min:1',
            'week_off_rules.*.weeks.*' => 'string|in:1,2,3,4,last',
            'grace_late_entry' => 'nullable|integer|min:0',
            'grace_early_exit' => 'nullable|integer|min:0',
            'post_shift_auto_checkout_cap_minutes' => 'nullable|integer|min:0|max:720',
            'color' => 'nullable|string|max:7',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'is_default' => 'boolean',
            'break_policy' => 'nullable|array',
            'ip_restrictions' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            $validated['week_off_rules'] = $this->normalizeWeekOffRules($validated['week_off_rules'] ?? []);

            if ($request->boolean('is_default') && empty($request->location_ids)) {
                 Shift::whereNull('location_ids')->where('id', '!=', $shift->id)->update(['is_default' => false]);
            }

            $shift->update($validated);

            $this->logger->log('attendance', 'shift_update', "Updated Shift: {$shift->name}", ['user_id' => auth()->id()]);

            DB::commit();

            return redirect()->back()->with('success', 'Shift updated successfully.')
                ->setStatusCode(303);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update shift: ' . $e->getMessage())
                ->setStatusCode(303);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $shift = Shift::findOrFail($id);

            $usageCount = \App\Models\AttendanceLog::where('shift_id', $shift->id)->count();
            if ($usageCount > 0) {
                 return response()->json(['message' => 'Cannot delete shift. It is used in historical attendance logs.'], 409);
            }

            $shift->delete(); 
            
            $this->logger->log('attendance', 'shift_delete', "Deleted Shift: {$shift->name}", ['user_id' => auth()->id()]);

            return redirect()->back()->with('success', 'Shift deleted successfully.')
                ->setStatusCode(303);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete shift: ' . $e->getMessage())
                ->setStatusCode(303);
        }
    }

    public function export()
    {
        $shifts = Shift::all();
        $csv = "Name,Code,Start Time,End Time,Work Days,Grace Late,Grace Early,Post Shift Auto Checkout Cap,Is Default\n";
        
        foreach ($shifts as $shift) {
            $days = is_array($shift->work_days) ? implode('|', $shift->work_days) : $shift->work_days;
            $csv .= "\"{$shift->name}\",\"{$shift->code}\",{$shift->start_time},{$shift->end_time},\"{$days}\",{$shift->grace_late_entry},{$shift->grace_early_exit},{$shift->post_shift_auto_checkout_cap_minutes},{$shift->is_default}\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="shifts_export.csv"',
        ]);
    }

    private function normalizeWeekOffRules(array $rules): ?array
    {
        $normalized = collect($rules)
            ->map(function ($rule) {
                $weekday = $rule['weekday'] ?? null;
                $weeks = array_values(array_unique($rule['weeks'] ?? []));

                if (!$weekday || empty($weeks)) {
                    return null;
                }

                $validWeeks = collect($weeks)
                    ->map(fn($w) => strtolower((string) $w))
                    ->filter(fn($w) => in_array($w, ['1', '2', '3', '4', 'last'], true))
                    ->values()
                    ->all();

                if (empty($validWeeks)) {
                    return null;
                }

                return [
                    'weekday' => $weekday,
                    'weeks' => $validWeeks,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return empty($normalized) ? null : $normalized;
    }
}
