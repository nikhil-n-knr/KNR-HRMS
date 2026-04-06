<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PhysicalRecord;
use App\Services\Documents\PhysicalDocumentService;
use Inertia\Inertia;

class PhysicalDocumentController extends Controller
{
    protected $service;

    public function __construct(PhysicalDocumentService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $docs = PhysicalRecord::with(['user:id,name', 'location:id,name'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Documents/PhysicalIndex', [
            'documents' => $docs,
            'locations' => \App\Models\PhysicalDocumentLocation::all(),
            'users' => \App\Models\User::select('id', 'name')->orderBy('name')->get() // Basic list for now
        ]);
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'outsider_name' => 'nullable|string|required_without:user_id', // Logic: One is required
            'document_type' => 'required|string',
            'location_id' => 'required|exists:physical_document_locations,id',
            'container_ref' => 'required|string'
        ]);

        $this->service->checkIn(
            $request->user_id,
            $request->document_type,
            $request->location_id,
            $request->container_ref,
            auth()->id(),
            $request->outsider_name
        );

        return back()->with('success', 'Document Checked In');
    }

    // --- Storage Builder ---
    
    public function config()
    {
        $locations = \App\Models\PhysicalDocumentLocation::whereNull('parent_id')->with('children')->get();
        return Inertia::render('Admin/Documents/StorageConfig', [
            'locations' => $locations
        ]);
    }

    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:Room,Cabinet,Rack,Shelf,Bin,Safe,Offsite_Storage',
            'parent_id' => 'nullable|exists:physical_document_locations,id'
        ]);

        \App\Models\PhysicalDocumentLocation::create($validated);
        
        return back()->with('success', 'Location Created');
    }

    public function destroyLocation(\App\Models\PhysicalDocumentLocation $location)
    {
         $location->delete();
         return back()->with('success', 'Location Deleted');
    }
}
