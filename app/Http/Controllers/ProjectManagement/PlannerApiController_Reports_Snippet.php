    /**
     * Reports API
     * Returns stats, table data, and charts for Planner Reports view.
     */
    public function reports(Request $request)
    {
        try {
            $projectId = $request->input('project_id');
            $start = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->startOfMonth();
            $end = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : now()->endOfMonth();

            // 1. Base Query for Timesheets (Actuals)
            $timesheetQuery = \App\Models\Timesheet::where('status', 'Approved') // Case sensitive? Standardize 'Approved'
                ->whereBetween('date', [$start, $end]);

            if ($projectId) {
                $timesheetQuery->where('project_id', $projectId);
            }

            $timesheets = $timesheetQuery->with(['employee.user', 'project', 'task'])->get();

            // 2. Base Query for Tasks (Scope/Plan)
            $taskQuery = \App\Models\Task::with(['assignees', 'sprint', 'stage'])
                ->whereNull('deleted_at'); // Soft delete check just in case

            if ($projectId) {
                $taskQuery->where('project_id', $projectId);
            }
            
            // For date range overlap logic on Tasks is tricky.
            // Usually Reports show "Tasks active in this period" or "All Tasks for Project".
            // If filtering by Date Range, we normally look at Actuals. 
            // For Scope, we might show ALL project scope if Project Filter is On.
            // If No Project Filter, showing ALL system tasks is too much.
            // Let's assume if Project ID is set, show Project Scope. If not, show Scope for tasks that had activity?
            // To keep it clean: Scope Stats usually relate to the Project context.
            
            $tasks = $taskQuery->get();

            // 3. Stats Calculation
            $totalHours = $tasks->sum('estimated_hours'); // Total Scope Allocated
            $totalPoints = $tasks->sum('scrum_points') ?? 0;
            
            // Actuals
            $investedHours = $timesheets->sum('hours_spent');
            $remainingHours = $totalHours - $investedHours; // Crude naive calc

            // Resource Count (Unique people in timesheets OR assignments)
            $activeResourceIds = $timesheets->pluck('employee_id')->unique();
            // Merge with assignees
            $assignedResourceIds = $tasks->flatMap(function($t) {
                return $t->assignees->pluck('id'); // User IDs
            })->unique();
            // Note: Timesheet has employee_id, Task has User ID via assignees (usually). 
            // Need to align. Timesheet->employee->user_id
            $timeLoggerUserIds = $timesheets->map(function($t) {
                return $t->employee ? $t->employee->user_id : null;
            })->filter()->unique();
            
            $allParticipantUserIds = $assignedResourceIds->merge($timeLoggerUserIds)->unique();
            $resourceCount = $allParticipantUserIds->count();

            // Avg Burn Rate (Daily)
            $daysDiff = $start->diffInDays($end) + 1;
            // Filter timesheets strictly within range (already done in query)
            $avgDaily = $daysDiff > 0 ? round($investedHours / $daysDiff, 1) : 0;

            $stats = [
                'total_hours' => (float) $totalHours, // Allocated
                'total_scope' => (float) $totalHours, // Same? Maybe "Scope" includes un-estimated count?
                'total_points' => (int) $totalPoints,
                'remaining_hours' => (float) $remainingHours,
                'holiday_hours' => 0, // Placeholder
                'resource_count' => $resourceCount,
                'avg_daily' => $avgDaily
            ];

            // 4. Table Data
            // Group by Employee + Project + Task? OR just flattened Timesheet list?
            // Front end suggests: Project | Task | Employee | Hours | Points
            // Points usually belong to Task. 
            // We can iterate Timesheets to show "Work Done".
            // OR iterate Tasks to show "Status".
            
            // User request: "STTS SHOULD BE ACCURATE AND ALIGNED"
            // Let's group by Task?
            // If we list Tasks, we see Plan/Act/Points.
            // If we list Timesheets, we see "Who worked on what".
            // Reports usually show "Who worked how much" (Timesheet Report) OR "Task Progress" (Status Report).
            // The columns in Vue are: Project, Task, Employee, Period Start/End, Hours, Points.
            // This looks like a flat list of "Effort".
            
            $tableData = $timesheets->map(function($t) {
                return [
                    'id' => $t->id,
                    'project' => $t->project ? $t->project->name : 'Unknown',
                    'task' => $t->task ? $t->task->title : 'General/Unassigned',
                    'task_status' => $t->task ? strtolower($t->task->status) : 'n/a', // todo, in_progress, done
                    'employee_name' => $t->employee ? ($t->employee->first_name . ' ' . $t->employee->last_name) : 'Unknown',
                    'employee_initials' => $t->employee ? substr($t->employee->first_name, 0, 1) . substr($t->employee->last_name, 0, 1) : 'NA',
                    'avatar' => $t->employee ? $t->employee->avatar : null,
                    'start_date' => $t->date->format('Y-m-d'),
                    'end_date' => $t->date->format('Y-m-d'), // Single day entry
                    'hours' => (float) $t->hours_spent,
                    'points' => $t->task ? $t->task->scrum_points : 0
                ];
            });

            // 5. Charts Data
            // A. Hours by Project
            $projectsChart = $timesheets->groupBy('project_name')->map->sum('hours_spent');
            
            // B. Hours by Employee
            $employeesChart = $timesheets->groupBy(function($t) {
                return $t->employee ? ($t->employee->first_name . ' ' . $t->employee->last_name) : 'Unknown';
            })->map->sum('hours_spent');

            // C. Points Leaderboard (Completed Tasks Only? Or Pro-rated?)
            // Usually "Points" are awarded when Task is DONE.
            // Let's look at Tasks marked as DONE in this period (or just all done tasks if no period filter on completion?)
            // To be safe, let's sum Points for tasks worked on in this period, grouped by Assignee? 
            // Or just sum points of Completed Tasks assigned to user.
            
            // Let's go with "Points earned by User" based on 'Done' tasks where they are assignee.
            // This requires a separate query on Tasks.
            
            $pointsQuery = \App\Models\Task::where('status', 'Done')
                ->whereHas('assignees'); // Filter valid assignees
            if ($projectId) $pointsQuery->where('project_id', $projectId);
            
            // Use date filter on 'updated_at' or 'end_date'?
            // $pointsQuery->whereBetween('updated_at', [$start, $end]); 
            
            $doneTasks = $pointsQuery->with('assignees')->get();
            $pointsChart = [];
            foreach ($doneTasks as $task) {
                $points = $task->scrum_points ?? 0;
                if ($points > 0 && $task->assignees->count() > 0) {
                    $splitPoints = $points / $task->assignees->count(); // Split points among assignees?
                    foreach ($task->assignees as $u) {
                        $name = $u->name; // User Name
                        if (!isset($pointsChart[$name])) $pointsChart[$name] = 0;
                        $pointsChart[$name] += $splitPoints;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'data' => $tableData,
                'charts' => [
                    'projects' => $projectsChart,
                    'employees' => $employeesChart,
                    'points' => $pointsChart
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->log('project_management', 'reports_error', $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
