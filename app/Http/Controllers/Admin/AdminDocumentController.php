<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;

class AdminDocumentController extends Controller
{
    use ApiResponser;

    /**
     * Display a global listing of ALL documents across the organization.
     */
    public function index(Request $request)
    {
        $query = EmployeeDocument::with('employee:id,first_name,last_name,employee_code,department_id');

        // Filter by Search (Title or Employee Name)
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('employee', function($e) use ($search) {
                      $e->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Category
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // Filter by Date Range
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $documents = $query->latest()->paginate(15);

        return $this->success($documents);
    }
}
