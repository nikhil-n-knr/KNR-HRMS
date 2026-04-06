<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Interview;
use Illuminate\Support\Facades\DB;
use App\Services\Infrastructure\LoggerService;

class InterviewController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }
    public function index(Request $request)
    {
        $this->logger->log('recruitment', 'view_interview_dashboard', 'View Interview Dashboard', ['user_id' => auth()->id()]);
        
        $user = auth()->user();
        
        // Base Query
        $query = Interview::with([
            'application.candidate', 
            'application.job', 
            'interviewer',
            'feedbacks'
        ]);

        // If not Admin/HR Manager, only show assigned interviews
        if (!$user->can('manage_recruitment')) {
             $query->where('interviewer_id', $user->id);
        }

        // Fetch All
        $interviews = $query->orderBy('scheduled_at', 'asc')->get();

        // Manual Grouping
         $myPending = $interviews->filter(function($i) use ($user) {
             return $i->status === 'Scheduled' && $i->interviewer_id === $user->id;
         })->values();

         $allUpcoming = $interviews->filter(function($i) {
             return $i->status === 'Scheduled';
         })->values();

         $completed = $interviews->filter(function($i) {
             return $i->status === 'Completed';
         })->sortByDesc('updated_at')->values();

        return Inertia::render('Talent/Interviews/Index', [
            'myPending' => $myPending,
            'allUpcoming' => $allUpcoming,
            'completed' => $completed
        ]);
    }
}
