<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BugTicket;
use App\Services\Infrastructure\LoggerService;

class BugTrackerController extends Controller
{
    /**
     * Get Open Bugs assigned to the user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $bugs = BugTicket::with(['project', 'priority', 'status'])
            ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->where('status_id', '!=', 5) // Assuming 5 is 'Closed' or 'Resolved'
            ->latest()
            ->paginate(15);

        return response()->json($bugs);
    }

    /**
     * Bulk Action: Close All Open Bugs
     */
    public function closeAll(Request $request)
    {
        $user = $request->user();
        
        $openBugs = BugTicket::whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->whereIn('status_id', [1, 2, 3, 4]) // Assuming these are open statuses
            ->get();

        $count = $openBugs->count();
        
        if ($count === 0) {
            return response()->json([
                'message' => 'No open bugs found to close.'
            ]);
        }

        // Mass update
        BugTicket::whereIn('id', $openBugs->pluck('id'))
            ->update([
                'status_id' => 5, // Closed
                'closed_at' => now(),
                'updated_at' => now()
            ]);

        \Log::context(['user_id' => $user->id, 'action' => 'mobile_bug_bulk_close']);
        LoggerService::info("Bulk Closed {$count} Bugs via Mobile App");

        return response()->json([
            'success' => true,
            'message' => "Successfully closed {$count} bugs.",
            'count' => $count
        ]);
    }

    /**
     * Update Single Bug Status
     */
    public function updateStatus(Request $request, BugTicket $bug)
    {
        $request->validate([
            'status_id' => 'required|integer',
            'comment' => 'nullable|string'
        ]);

        if (!$bug->assignees()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized.');
        }

        $bug->update([
            'status_id' => $request->status_id,
            'closed_at' => $request->status_id == 5 ? now() : $bug->closed_at
        ]);

        if ($request->comment) {
            $bug->activities()->create([
                'user_id' => auth()->id(),
                'activity_type' => 'status_change',
                'description' => $request->comment
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bug status updated.'
        ]);
    }
}
