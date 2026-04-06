<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BugTicketViewController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            \App\Models\BugTicketView::where('user_id', auth()->id())->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'filters' => 'required|array',
        ]);

        $view = \App\Models\BugTicketView::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'filters' => $request->filters,
        ]);

        return response()->json($view, 201);
    }

    public function destroy($id)
    {
        $view = \App\Models\BugTicketView::where('user_id', auth()->id())->findOrFail($id);
        $view->delete();

        return response()->json(['message' => 'View deleted successfully']);
    }
}
