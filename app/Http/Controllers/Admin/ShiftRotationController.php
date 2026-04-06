<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShiftRotation;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class ShiftRotationController extends Controller
{
    public function index()
    {
        return response()->json(ShiftRotation::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'frequency' => 'required|in:weekly,bi-weekly,monthly',
            'pattern' => 'required|array',
            'pattern.*' => 'required|string', // Shift Names
        ]);

        $rotation = ShiftRotation::create($validated);

        return response()->json($rotation, 201);
    }

    public function update(Request $request, ShiftRotation $rotation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'frequency' => 'required|in:weekly,bi-weekly,monthly',
            'pattern' => 'required|array',
            'pattern.*' => 'required|string',
        ]);

        $rotation->update($validated);

        return response()->json($rotation);
    }

    public function destroy(ShiftRotation $rotation)
    {
        $rotation->delete();
        return response()->json(null, 204);
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'rotation_id' => 'required|exists:shift_rotations,id',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:users,id',
            'start_date' => 'required|date',
        ]);

        $rotation = ShiftRotation::find($validated['rotation_id']);
        
        // Use DB transaction used for bulk insert/update
        DB::transaction(function () use ($validated) {
            foreach ($validated['employee_ids'] as $empId) {
                // Detach existing if any (Assuming one rotation per user for simplicity)
                DB::table('employee_rotation')->where('employee_id', $empId)->delete();
                
                DB::table('employee_rotation')->insert([
                    'employee_id' => $empId,
                    'rotation_id' => $validated['rotation_id'],
                    'start_date' => $validated['start_date'],
                    'current_step' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json(['message' => 'Rotations assigned successfully']);
    }
}
