<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;

class PlannerController extends Controller
{
    public function index()
    {
        return Inertia::render('Project/Planner/Index', [
            'projects' => Project::select('id', 'name')->get()
        ]);
    }
}
