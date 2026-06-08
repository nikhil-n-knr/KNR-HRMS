<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SalaryStructure;
use App\Models\SalaryComponent;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SalaryStructureController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/SalaryStructures/Index', [
            'structures' => SalaryStructure::with('components')->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/SalaryStructures/Edit', [
            'structure' => new SalaryStructure(),
            'components' => []
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'components' => 'required|array|min:1',
            'components.*.name' => 'required|string',
            'components.*.type' => 'required|in:earning,deduction',
        ]);

        DB::transaction(function () use ($request) {
            $structure = SalaryStructure::create($request->only('name', 'description'));

            foreach ($request->components as $idx => $comp) {
                $structure->components()->create([
                    'name' => $comp['name'],
                    'type' => $comp['type'],
                    'calculation_type' => $comp['calculation_type'] ?? 'fixed',
                    'value' => $comp['value'] ?? 0,
                    'order' => $idx + 1
                ]);
            }
        });

        return to_route('admin.salary-structures.index')->with('success', 'Structure Created');
    }

    public function edit(SalaryStructure $salary_structure)
    {
        $salary_structure->load('components');
        return Inertia::render('Admin/SalaryStructures/Edit', [
            'structure' => $salary_structure,
            'components' => $salary_structure->components
        ]);
    }

    public function update(Request $request, SalaryStructure $salary_structure)
    {
        // Full Replacement Logic for Components (Simplest for now)
        DB::transaction(function () use ($request, $salary_structure) {
            $salary_structure->update($request->only('name', 'description'));
            $salary_structure->components()->delete();
             foreach ($request->components as $idx => $comp) {
                $salary_structure->components()->create([
                    'name' => $comp['name'],
                    'type' => $comp['type'],
                    'calculation_type' => $comp['calculation_type'] ?? 'fixed',
                    'value' => $comp['value'] ?? 0,
                    'order' => $idx + 1
                ]);
            }
        });
        
        return to_route('admin.salary-structures.index')->with('success', 'Structure Updated');
    }
}
