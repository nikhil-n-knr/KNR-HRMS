<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointRule;
use App\Models\Badge;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GamificationController extends Controller
{
    use \App\Traits\HasAttendanceHubData;

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $data = $this->getHubBaseData('gamification', $tenantId);
        
        $data['rules'] = PointRule::orderBy('event_category')->get();
        $data['badges'] = Badge::all();

        return Inertia::render('Admin/Attendance/Hub', $data);
    }

    public function updateRule(Request $request, PointRule $pointRule)
    {
        $validated = $request->validate([
            'points' => 'required|integer',
            'is_active' => 'boolean',
            'condition_logic' => 'nullable|string'
        ]);

        $pointRule->update($validated);

        return to_route('admin.attendance.gamification')->with('success', 'Rule updated.')
            ->setStatusCode(303);
    }

    public function storeBadge(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string',
            'points_bonus' => 'required|integer',
            'slug' => 'required|string|unique:badges,slug'
        ]);

        Badge::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'criteria_description' => $validated['description'], // Map description to criteria_description
            'icon' => $validated['icon'] ?? '🏆',
            'points_bonus' => $validated['points_bonus']
        ]);

        return to_route('admin.attendance.gamification')->with('success', 'Badge created.')
            ->setStatusCode(303);
    }
    
    public function destroyBadge(Badge $badge)
    {
        $badge->delete();
        return to_route('admin.attendance.gamification')->with('success', 'Badge removed.')
            ->setStatusCode(303);
    }

    /**
     * Fallback for PUT /gamification (Handle ID from body)
     */
    public function updateFallback(Request $request)
    {
        // Try to find ID in request
        $id = $request->input('id');
        if (!$id) {
            abort(400, 'Missing Rule ID in request body');
        }

        $rule = PointRule::findOrFail($id);
        return $this->updateRule($request, $rule);
    }
}
