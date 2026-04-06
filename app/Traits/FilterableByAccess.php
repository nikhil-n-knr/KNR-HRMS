<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FilterableByAccess
{
    /**
     * Apply standard employee filters to the query.
     * 
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function scopeApplyStandardFilters(Builder $query, Request $request)
    {
        // 1. Search (Name, Employee ID, User ID)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  // If joined with users table, handle user name? 
                  // Assuming this trait is applied on Employee model mainly.
                  ->orWhereHas('user', function($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Department Filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // 3. Location Filter
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // 4. Role Filter
        if ($request->filled('role_id')) {
            $roleId = $request->role_id;
            $query->whereHas('user.roles', function($q) use ($roleId) {
                $q->where('id', $roleId);
            });
        }
        
        // 5. Role Name Filter (Optional)
        if ($request->filled('role_name')) {
            $roleName = $request->role_name;
            $query->whereHas('user.roles', function($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        return $query;
    }
}
