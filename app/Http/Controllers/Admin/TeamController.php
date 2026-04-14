<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    use \App\Traits\HasAttendanceHubData;
    /**
     * Display the Teams Manager (via Unified Hub).
     */
    public function index()
    {
        $data = $this->getHubBaseData('teams');
            
        return Inertia::render('Admin/Attendance/Hub', array_merge($data, [
            'tab' => 'teams',
        ]));
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'parent_team_id' => 'nullable|exists:teams,id',
            'role_id' => 'nullable|exists:roles,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id'
        ]);

        $team = Team::create($validated);

        if (!empty($validated['member_ids'])) {
            User::whereIn('id', $validated['member_ids'])->update(['team_id' => $team->id]);
            
            if ($team->role_id) {
                foreach ($validated['member_ids'] as $uid) {
                    $user = User::find($uid);
                    $user->roles()->syncWithoutDetaching([
                        $team->role_id => ['assigned_by' => auth()->id()]
                    ]);
                }
            }
        }

        return to_route('admin.attendance.teams.index')->with('success', 'Team created successfully.')
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
            'parent_team_id' => 'nullable|exists:teams,id',
            'role_id' => 'nullable|exists:roles,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id'
        ]);

        $team->update($validated);

        // Update members:
        // 1. Remove team_id from users who were in the team but aren't anymore in the selection
        User::where('team_id', $team->id)
            ->whereNotIn('id', $validated['member_ids'] ?? [])
            ->update(['team_id' => null]);

        // 2. Set team_id for current selected members
        if (!empty($validated['member_ids'])) {
            User::whereIn('id', $validated['member_ids'])->update(['team_id' => $team->id]);
            
            if ($team->role_id) {
                 foreach ($validated['member_ids'] as $uid) {
                    $user = User::find($uid);
                    $user->roles()->syncWithoutDetaching([
                        $team->role_id => ['assigned_by' => auth()->id()]
                    ]);
                }
            }
        }

        return to_route('admin.attendance.teams.index')->with('success', 'Team updated.')
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
        return to_route('admin.attendance.teams.index')->with('success', 'Member removed.')
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
