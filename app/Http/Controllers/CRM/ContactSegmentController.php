<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\ContactSegment;
use Illuminate\Http\Request;

class ContactSegmentController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $segments = ContactSegment::where('tenant_id', $tenantId)
            ->where(function($q) {
                $q->where('is_public', true)
                  ->orWhere('created_by', auth()->id());
            })
            ->with('creator')
            ->get();

        return response()->json($segments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'criteria' => 'required|array',
        ]);

        ContactSegment::create([
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
            ...$request->all()
        ]);

        return redirect()->back()->with('success', 'Segment created successfully.');
    }

    public function show(ContactSegment $contactSegment)
    {
        // Return both segment definition and the paginated contacts
        $contacts = $contactSegment->getContactsQuery()->paginate(20);
        
        return response()->json([
            'segment' => $contactSegment,
            'contacts' => $contacts
        ]);
    }

    public function update(Request $request, ContactSegment $contactSegment)
    {
        $this->authorize('update', $contactSegment);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'criteria' => 'required|array',
        ]);

        $contactSegment->update($request->all());

        return response()->json($contactSegment);
    }

    public function destroy(ContactSegment $contactSegment)
    {
        $this->authorize('delete', $contactSegment);
        $contactSegment->delete();
        return response()->json(['message' => 'Segment deleted']);
    }
}
