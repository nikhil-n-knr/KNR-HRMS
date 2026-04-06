<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Performance\AppraisalCycle;
use Inertia\Inertia;

class AppraisalCycleController extends Controller
{
    public function index()
    {
        return Inertia::render('Performance/Admin/Cycles/Index', [
            'cycles' => AppraisalCycle::orderBy('start_date', 'desc')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'self_review_deadline' => 'nullable|date|before:end_date',
            'is_active' => 'boolean'
        ]);

        if ($validated['is_active'] ?? false) {
            AppraisalCycle::where('is_active', true)->update(['is_active' => false]);
        }

        AppraisalCycle::create($validated);

        return redirect()->back()->with('success', 'Appraisal Cycle Created.');
    }
}
