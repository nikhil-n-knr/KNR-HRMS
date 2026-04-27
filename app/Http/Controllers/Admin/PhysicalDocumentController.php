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

    public function update(Request $request, PhysicalRecord $record)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'outsider_name' => 'nullable|string|max:255|required_without:user_id',
            'location_id' => 'required|exists:physical_document_locations,id',
            'container_ref' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['user_id'])) {
            $validated['outsider_name'] = null;
        }

        $record->update($validated);

        return back()->with('success', 'Document Updated');
    }

    public function destroy(PhysicalRecord $record)
    {
        $record->delete();

        return back()->with('success', 'Document Deleted');
    }

    public function checkout(PhysicalRecord $record)
    {
        try {
            $this->service->checkout($record->id, auth()->id());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Document Checked Out');
    }

    public function returnToCustody(PhysicalRecord $record)
    {
        try {
            $this->service->returnToCustody($record->id, auth()->id());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Document Returned to Custody');
    }

    public function markMissing(PhysicalRecord $record)
    {
        try {
            $this->service->markMissing($record->id);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Document Marked Missing');
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
