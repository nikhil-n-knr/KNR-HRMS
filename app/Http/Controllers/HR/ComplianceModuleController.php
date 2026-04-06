<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\ComplianceStateRule;
use App\Models\ComplianceLicence;
use App\Models\MinimumWage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplianceModuleController extends Controller
{
    // Module 1: Branches
    public function getBranches()
    {
        return response()->json(Location::orderBy('is_hq', 'desc')->get());
    }

    public function storeBranch(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:locations,code',
            'city' => 'required|string',
            'state_code' => 'required|string|size:2',
            'pt_enabled' => 'boolean',
            'lwf_enabled' => 'boolean',
            'is_hq' => 'boolean',
        ]);

        $location = Location::create($validated);
        return response()->json($location);
    }

    public function updateBranch(Request $request, Location $location)
    {
        $validated = $request->validate([
            'pt_enabled' => 'boolean',
            'lwf_enabled' => 'boolean',
            'state_code' => 'required|string|size:2',
            'city' => 'nullable|string',
        ]);

        $location->update($validated);
        
        \App\Jobs\UpdateEmployeeStateMappingJob::dispatch($location);

        return response()->json($location->fresh());
    }

    public function destroyBranch(Location $location)
    {
        // Check for dependencies (employees)
        if ($location->employees()->exists()) {
            return response()->json(['message' => 'Cannot delete branch with active employees.'], 422);
        }
        $location->delete();
        return response()->json(['success' => true]);
    }

    // Module 2: Rules
    public function getRules($state_code)
    {
        return response()->json(
            ComplianceStateRule::where('state_code', $state_code)->get()->keyBy('component')
        );
    }

    public function saveRule(Request $request)
    {
        $validated = $request->validate([
            'state_code' => 'required|string|size:2',
            'component' => 'required|string',
            'rules' => 'required|array',
        ]);

        $componentMap = [
            'pt' => 'professional_tax',
            'lwf' => 'lwf',
            'leave' => 'leave_mandate'
        ];

        $component = $componentMap[$validated['component']] ?? $validated['component'];

        $rule = ComplianceStateRule::updateOrCreate(
            ['state_code' => $validated['state_code'], 'component' => $component],
            ['rules' => $validated['rules']]
        );

        // Invalidate Cache
        \Illuminate\Support\Facades\Cache::forget("compliance_rules_{$validated['state_code']}");

        return response()->json($rule);
    }

    public function deleteRule(Request $request)
    {
        $validated = $request->validate([
            'state_code' => 'required|string|size:2',
            'component' => 'required|string',
        ]);

        ComplianceStateRule::where('state_code', $validated['state_code'])
            ->where('component', $validated['component'])
            ->delete();

        \Illuminate\Support\Facades\Cache::forget("compliance_rules_{$validated['state_code']}");

        return response()->json(['success' => true]);
    }

    // Module 3: Licences
    public function getLicences()
    {
        return response()->json(ComplianceLicence::with('location')->orderBy('expiry_date', 'asc')->get());
    }

    public function uploadLicence(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'state_code' => 'required|string|size:2',
            'location_id' => 'nullable|exists:locations,id',
            'expiry_date' => 'required|date',
            'file' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120',
        ]);

        $data = $request->only(['name', 'state_code', 'location_id', 'expiry_date']);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('compliance/licences', 'public');
            $data['document_path'] = $path;
        }

        $licence = ComplianceLicence::create($data);

        return response()->json($licence);
    }

    public function destroyLicence(ComplianceLicence $licence)
    {
        if ($licence->document_path) {
            Storage::disk('public')->delete($licence->document_path);
        }
        $licence->delete();
        return response()->json(['success' => true]);
    }

    // Module 4: Minimum Wage
    public function getMinimumWages()
    {
        return response()->json(MinimumWage::orderBy('state_code', 'asc')->get());
    }

    public function saveMinimumWage(Request $request)
    {
        $validated = $request->validate([
            'state_code' => 'required|string|size:2',
            'skill_level' => 'required|string',
            'basic_wage' => 'required|numeric',
            'vda' => 'required|numeric',
            'effective_from' => 'required|date',
            'zone' => 'nullable|string',
        ]);

        $wage = MinimumWage::updateOrCreate(
            ['state_code' => $validated['state_code'], 'skill_level' => $validated['skill_level'], 'zone' => $validated['zone']],
            ['basic_wage' => $validated['basic_wage'], 'vda' => $validated['vda'], 'effective_from' => $validated['effective_from']]
        );

        return response()->json($wage);
    }

    public function destroyMinimumWage(MinimumWage $minimumWage)
    {
        $minimumWage->delete();
        return response()->json(['success' => true]);
    }

    public function validateSalary(Request $request)
    {
        $validated = $request->validate([
            'state_code' => 'required|string|size:2',
            'skill_level' => 'required|string',
            'proposed_basic' => 'required|numeric',
        ]);

        $minWage = MinimumWage::where('state_code', $validated['state_code'])
            ->where('skill_level', $validated['skill_level'])
            ->first();

        if (!$minWage) {
            return response()->json(['valid' => true, 'message' => 'No specific rule found for this category.']);
        }

        $totalMin = $minWage->basic_wage + $minWage->vda;
        if ($validated['proposed_basic'] < $totalMin) {
            return response()->json([
                'valid' => false,
                'min_required' => $totalMin,
                'message' => "Salary is below the minimum wage requirement of ₹" . number_format($totalMin, 2) . " for {$validated['skill_level']} workers in {$validated['state_code']}."
            ], 422);
        }

        return response()->json(['valid' => true]);
    }
}
