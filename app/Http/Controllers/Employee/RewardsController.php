<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\Employee\RewardsAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RewardsController extends Controller
{
    public function index(Request $request, RewardsAnalyticsService $analyticsService, ?string $uuid = null)
    {
        $user = $request->user();
        $viewerEmployee = $user->employee;
        $isSuperAdmin = $user->hasRole('Super Admin');

        if (!$viewerEmployee && !$isSuperAdmin) {
            abort(403, 'Employee record not available for rewards access.');
        }

        $employee = $uuid
            ? Employee::where('uuid', $uuid)->firstOrFail()
            : ($viewerEmployee ?? Employee::where('user_id', $user->id)->firstOrFail());

        if (!$isSuperAdmin && (int) $employee->id !== (int) $viewerEmployee?->id) {
            abort(403, 'Unauthorized rewards access.');
        }

        $payload = $analyticsService->build(
            $employee,
            $request->query('date_from'),
            $request->query('date_to'),
            $request->query('source')
        );

        return Inertia::render('Employee/Rewards/Index', [
            'employee' => [
                'id' => $employee->id,
                'uuid' => $employee->uuid,
                'name' => $employee->name,
                'designation' => $employee->designation,
                'avatar_url' => $employee->avatar_url,
            ],
            'summary' => $payload['summary'],
            'charts' => $payload['charts'],
            'badges' => $payload['badges'],
            'ledger' => $payload['ledger'],
            'filters' => $payload['filters'],
            'sources' => ['all', 'attendance', 'scrum', 'performance', 'learning', 'manual', 'other'],
        ]);
    }
}
