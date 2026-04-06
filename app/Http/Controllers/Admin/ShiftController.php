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
            'grace_late_entry' => 'nullable|integer|min:0',
            'grace_early_exit' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:7', // Hex code
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'is_default' => 'boolean',
            'break_policy' => 'nullable|array', // JSON
            'ip_restrictions' => 'nullable|array' // JSON
        ]);

        try {
            DB::beginTransaction();

            if ($request->boolean('is_default') && empty($request->location_ids)) {
                Shift::whereNull('location_ids')->update(['is_default' => false]);
            }

            $validated['tenant_id'] = 1; 
            
            $shift = Shift::create($validated);

            $this->logger->log('attendance', 'shift_create', "Created Shift: {$shift->name}", ['user_id' => auth()->id()]);

            DB::commit();

            return response()->json(['message' => 'Shift created successfully.', 'data' => $shift], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create shift: ' . $e->getMessage()], 500);
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
            'grace_late_entry' => 'nullable|integer|min:0',
            'grace_early_exit' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:7',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'is_default' => 'boolean',
            'break_policy' => 'nullable|array',
            'ip_restrictions' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            if ($request->boolean('is_default') && empty($request->location_ids)) {
                 Shift::whereNull('location_ids')->where('id', '!=', $shift->id)->update(['is_default' => false]);
            }

            $shift->update($validated);

            $this->logger->log('attendance', 'shift_update', "Updated Shift: {$shift->name}", ['user_id' => auth()->id()]);

            DB::commit();

            return response()->json(['message' => 'Shift updated successfully.', 'data' => $shift], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update shift: ' . $e->getMessage()], 500);
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

            return response()->json(['message' => 'Shift deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete shift: ' . $e->getMessage()], 500);
        }
    }

    public function export()
    {
        $shifts = Shift::all();
        $csv = "Name,Code,Start Time,End Time,Work Days,Grace Late,Grace Early,Is Default\n";
        
        foreach ($shifts as $shift) {
            $days = is_array($shift->work_days) ? implode('|', $shift->work_days) : $shift->work_days;
            $csv .= "\"{$shift->name}\",\"{$shift->code}\",{$shift->start_time},{$shift->end_time},\"{$days}\",{$shift->grace_late_entry},{$shift->grace_early_exit},{$shift->is_default}\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="shifts_export.csv"',
        ]);
    }
}
