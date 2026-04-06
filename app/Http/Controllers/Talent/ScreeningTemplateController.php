<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\ScreeningTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScreeningTemplateController extends Controller
{
    public function index()
    {
        return Inertia::render('Talent/Screening/Templates', [
            'templates' => ScreeningTemplate::with('creator:id,name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'questions' => 'required|array', // JSON Validation happens on frontend for structure
        ]);

        ScreeningTemplate::create([
            'name' => $request->name,
            'questions' => $request->questions,
            'created_by' => auth()->id()
        ]);

        return back()->with('success', 'Screening template created.');
    }

    public function update(Request $request, ScreeningTemplate $screening_template)
    {
        \Log::info('Updating Template', ['id' => $screening_template->id, 'data' => $request->all()]);

        $request->validate([
             'name' => 'required|string',
             'questions' => 'required|array'
        ]);

        $screening_template->update($request->only(['name', 'questions']));

        return back()->with('success', 'Template updated.');
    }

    public function destroy(ScreeningTemplate $screening_template)
    {
        // Prevent delete if attached to jobs
        if ($screening_template->jobs()->exists()) {
             return back()->with('error', 'Cannot delete: Template is attached to active jobs.');
        }
        
        $screening_template->delete();
        return back()->with('success', 'Template deleted.');
    }
}
