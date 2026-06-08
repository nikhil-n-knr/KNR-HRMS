<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VisitorPurposeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // For Admin Settings Page
        $purposes = DB::table('visitor_purposes')->orderBy('id')->get();
        
        // Parse JSON configs for frontend
        $purposes = $purposes->map(function($p) {
            $p->form_config = json_decode($p->form_config, true);
            return $p;
        });

        return Inertia::render('Admin/Visitors/Settings/FormBuilder', [
            'purposes' => $purposes
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'form_config' => 'required|array',
            'is_active' => 'boolean'
        ]);

        DB::table('visitor_purposes')
            ->where('id', $id)
            ->update([
                'form_config' => json_encode($request->form_config),
                'is_active' => $request->is_active,
                'updated_at' => now()
            ]);

        return back()->with('success', 'Form configuration updated successfully.');
    }
}
