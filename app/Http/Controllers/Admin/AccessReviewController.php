<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Core\ModuleService;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;

class AccessReviewController extends Controller
{
    use ApiResponser;

    protected $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    public function index(Request $request) 
    {
        if (!auth()->user()->hasPermission('user_management.permission.view')) {
            abort(403);
        }

        // Just return list of users for dropdown
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->select('id', 'name', 'email')
            ->get();

        return $this->success($users);
    }

    public function show(Request $request, User $user)
    {
        if (!auth()->user()->hasPermission('user_management.permission.view')) {
            abort(403);
        }

        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $user->load('roles');

        $modules = $this->moduleService->getActiveModules();
        $review = [];

        foreach ($modules as $mod) {
            $modReview = [
                'name' => $mod->name,
                'sub_modules' => []
            ];

            foreach ($mod->sub_modules as $sub) {
                $subReview = [
                    'name' => $sub->name,
                    'permissions' => []
                ];

                $actions = ['view', 'create', 'update', 'delete', 'approve', 'export']; // Standard + others if dynamic?

                foreach ($actions as $action) {
                    $permKey = "{$mod->key}.{$sub->key}.{$action}";
                    // We check if user has this permission and what scope
                    if ($user->hasPermission($permKey)) {
                         $scope = $user->getPermissionScope($permKey);
                         $subReview['permissions'][] = [
                             'action' => $action,
                             'scope' => $scope,
                             'allowed' => true
                         ];
                    } else {
                         // Optional: exclude denied?
                         $subReview['permissions'][] = [
                             'action' => $action,
                             'scope' => null,
                             'allowed' => false
                         ];
                    }
                }
                $modReview['sub_modules'][] = $subReview;
            }
            $review[] = $modReview;
        }

        return $this->success([
            'user' => $user->only(['id', 'name', 'email']),
            'roles' => $user->roles->pluck('name'),
            'access_matrix' => $review
        ]);
    }
}
