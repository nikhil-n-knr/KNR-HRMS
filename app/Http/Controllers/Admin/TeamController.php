<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Display the Teams Manager (via Unified Hub).
     */
    public function index()
    {
        // Reuse the Policy Hub logic but activate 'teams' tab
        $teams = Team::with(['manager:id,name', 'parent:id,name', 'members:id,name,email,team_id']) // Eager load members
            ->withCount('members')
            ->get();
            
        // We need to fetch other tabs' data too if we want full switching validation
        // OR we can lazy load them. For now, let's load what's needed for the hub.
        // Actually, the Hub logic in PolicyController might be reusable or we just replicate the render.
        // To avoid code duplication, we should probably have a 'AdminHubService' or similar.
        // For now, let's just minimal load and assume switching tabs might trigger their own fetches if configured as visits,
        // BUT the Link components uses Inertia visits, so yes, we need to load data per controller.
        
        return Inertia::render('Admin/Attendance/Hub', [
            'tab' => 'teams', // This triggers the sidebar selection
            'teams' => $teams,
            'users' => [], // Manager.vue requires it but search is async.
        ]);
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'parent_team_id' => 'nullable|exists:teams,id'
        ]);

        Team::create($validated);

        return to_route('admin.attendance.teams')->with('success', 'Team created successfully.')
            ->setStatusCode(303);
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'parent_team_id' => 'nullable|exists:teams,id'
        ]);

        $team->update($validated);

        return to_route('admin.attendance.teams')->with('success', 'Team updated.')
            ->setStatusCode(303);
    }

    /**
     * Remove the specified team.
     */
    public function destroy(Team $team)
    {
        $team->delete();
        return redirect()->back()->with('success', 'Team deleted.')
            ->setStatusCode(303);
    }

    public function addMember(Request $request, Team $team)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->update(['team_id' => $team->id]);

        return to_route('admin.attendance.teams')->with('success', 'Member added to team.')
            ->setStatusCode(303);
    }

    public function removeMember(Team $team, User $user)
    {
        if ($user->team_id !== $team->id) {
            return back()->with('error', 'User is not in this team.');
        }

        $user->update(['team_id' => null]);
        return to_route('admin.attendance.teams')->with('success', 'Member removed.')
            ->setStatusCode(303);
    }

    public function bulkAddMembers(Request $request, Team $team)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        User::whereIn('id', $validated['user_ids'])->update(['team_id' => $team->id]);

        return to_route('admin.attendance.teams')->with('success', count($validated['user_ids']) . ' members added to team.')
            ->setStatusCode(303);
    }

    /**
     * Fallback for PUT /teams (Handle ID from body)
     */
    public function updateFallback(Request $request)
    {
         // Try to find ID in request or body
         // Usually Vue form.put sends it in payload if we configured it right, OR we can grab it from route param if it existed.
         // But here we assume route param is missing.
         
         $id = $request->input('id');
         if (!$id) {
             // Try to see if there's a 'team' input
             $id = $request->input('team');
         }

         if (!$id) {
             abort(400, 'Missing Team ID in request body');
         }

         $team = Team::findOrFail($id);
         return $this->update($request, $team);
    }
}
