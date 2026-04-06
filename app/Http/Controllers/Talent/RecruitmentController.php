<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobPosting;
use App\Models\JobCategory;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Support\Str;
use Inertia\Inertia;

use Illuminate\Support\Facades\DB;
use App\Services\Infrastructure\LoggerService;

class RecruitmentController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }
    /**
     * Display the Talent Management Hub.
     */
    public function index(Request $request)
    {
        // 1. Fetch Jobs with filtering
        // Load screeningTemplate name
        $query = JobPosting::with(['category', 'department', 'location', 'creator', 'screeningTemplate'])
                    ->latest();
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $jobs = $query->paginate(10);
        
        // 2. Fetch Dashboard Stats (if needed for the hub)
        $stats = [
            'active_jobs' => JobPosting::where('status', 'Published')->count(),
            'total_candidates' => \App\Models\Candidate::count(),
            'interviews_today' => \App\Models\Interview::whereDate('scheduled_at', today())->count(),
        ];

        return Inertia::render('Talent/Hub', [
            'jobs' => $jobs,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
            'screening_templates' => \App\Models\ScreeningTemplate::select('id', 'name')->get(), 
        ]);
    }

    public function create()
    {
        return Inertia::render('Talent/Jobs/Create', [
            'categories' => JobCategory::where('status', true)->whereNull('parent_id')->with('children')->get(),
            'departments' => Department::select('id', 'name')->get(),
            'locations' => Location::select('id', 'name', 'city')->get(),
            'users' => \App\Models\User::select('id', 'name', 'email')->get(), 
            'teams' => \App\Models\Team::select('id', 'name')->get(),
            'screening_templates' => \App\Models\ScreeningTemplate::select('id', 'name')->get(),
            'skills_list' => $this->getSkillsList(),
            'nextId' => $this->generateJobId(),
        ]);
    }

    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'location_id' => 'required|exists:locations,id',
            'job_category_id' => 'required|exists:job_categories,id',
            'screening_template_id' => 'nullable|exists:screening_templates,id',
            'type' => 'required|string',
            'description' => 'required|string',
            'notification_config' => 'nullable|array',
            'required_documents' => 'nullable|array',
            'valid_through' => 'nullable|date',
            'min_experience' => 'nullable|integer|min:0',
            'max_experience' => 'nullable|integer|gt:min_experience',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|gt:salary_min',
            'salary_currency' => 'nullable|string|size:3',
            'skills' => 'nullable|array', 
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Sync new skills to DB
                if (!empty($validated['skills'])) {
                    foreach ($validated['skills'] as $skillName) {
                        \App\Models\Skill::firstOrCreate(['name' => $skillName]);
                    }
                }

                $job = new JobPosting($validated);
                if (empty($job->salary_currency)) $job->salary_currency = 'INR';
                $job->job_code = $this->generateJobId(); 
                $job->created_by = auth()->id();
                $job->save();

                $job->save();

                $this->logger->log('recruitment', 'job_create', 'Job Posted Successfully: ' . $job->title, ['job_id' => $job->id, 'job_code' => $job->job_code]);
            });
        } catch (\Exception $e) {
            $this->logger->log('recruitment', 'job_create_error', 'Job Creation Failed', ['error' => $e->getMessage()], 'error');
            return back()->withErrors(['error' => 'Failed to create job posting.']);
        }

        return redirect()->route('talent.jobs.index')->with('success', 'Job created successfully.');
    }

    public function edit(JobPosting $job)
    {
        return Inertia::render('Talent/Jobs/Edit', [
            'job' => $job, 
            'categories' => JobCategory::where('status', true)->whereNull('parent_id')->with('children')->get(),
            'departments' => Department::select('id', 'name')->get(),
            'locations' => Location::select('id', 'name', 'city')->get(),
            'users' => \App\Models\User::select('id', 'name', 'email')->get(), 
            'teams' => \App\Models\Team::select('id', 'name')->get(),
            'screening_templates' => \App\Models\ScreeningTemplate::select('id', 'name')->get(),
            'skills_list' => $this->getSkillsList(), 
        ]);
    }

    public function update(Request $request, JobPosting $job)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'location_id' => 'required|exists:locations,id',
            'job_category_id' => 'required|exists:job_categories,id',
            'screening_template_id' => 'nullable|exists:screening_templates,id',
            'type' => 'required|string',
            'description' => 'required|string',
            'notification_config' => 'nullable|array',
            'required_documents' => 'nullable|array',
            'status' => 'required|in:Draft,Published',
            'valid_through' => 'nullable|date',
            'min_experience' => 'nullable|integer|min:0',
            'max_experience' => 'nullable|integer|gt:min_experience',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|gt:salary_min',
            'salary_currency' => 'nullable|string|size:3',
            'skills' => 'nullable|array', 
        ]);

        try {
            DB::transaction(function () use ($job, $validated) {
                // Sync new skills
                 if (!empty($validated['skills'])) {
                    foreach ($validated['skills'] as $skillName) {
                        \App\Models\Skill::firstOrCreate(['name' => $skillName]);
                    }
                }

                if (empty($validated['salary_currency'])) $validated['salary_currency'] = 'INR';
                $job->update($validated);
                if (empty($validated['salary_currency'])) $validated['salary_currency'] = 'INR';
                $job->update($validated);
                $this->logger->log('recruitment', 'job_update', 'Job Updated Successfully: ' . $job->title, ['job_id' => $job->id, 'job_code' => $job->job_code]);
            });
        } catch (\Exception $e) {
            $this->logger->log('recruitment', 'job_update_error', 'Job Update Failed', ['error' => $e->getMessage(), 'job_id' => $job->id], 'error');
            return back()->withErrors(['error' => 'Failed to update job posting.']);
        }

        return redirect()->route('talent.jobs.index')->with('success', 'Job updated successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:job_postings,id',
            'action' => 'required|in:delete,activate,deactivate,extend,assign_template', 
            'date' => 'required_if:action,extend|date|after:today',
            'template_id' => 'required_if:action,assign_template|exists:screening_templates,id' 
        ]);

        $count = 0;
        
        DB::transaction(function () use ($request, &$count) {
            $query = JobPosting::whereIn('id', $request->ids);
            
            if ($request->action === 'delete') {
                $count = $query->delete();
                $this->logger->log('recruitment', 'job_bulk_delete', 'Jobs Bulk Soft-Deleted', ['ids' => $request->ids, 'count' => $count]);
            } elseif ($request->action === 'activate') {
                $count = $query->update(['status' => 'Published']);
            } elseif ($request->action === 'deactivate') {
                $count = $query->update(['status' => 'Draft']); 
            } elseif ($request->action === 'extend') {
                $count = $query->update(['valid_through' => $request->date]);
            } elseif ($request->action === 'assign_template') {
                $count = $query->update(['screening_template_id' => $request->template_id]);
            }
            
            if ($request->action !== 'delete') {
                $this->logger->log('recruitment', "job_bulk_{$request->action}", "Jobs Bulk Action: {$request->action}", ['count' => $count, 'ids' => $request->ids]);
            }
        });

        return back()->with('success', "Action completed on {$count} jobs.");
    }

    private function getSkillsList()
    {
        return \App\Models\Skill::orderBy('name')->pluck('name')->toArray();
    }
    
    // --- Helpers ---

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_categories,name',
            'parent_id' => 'nullable|exists:job_categories,id'
        ]);

        $category = JobCategory::create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'],
            'status' => true
        ]);

        return back()->with('success', 'Category Created');
    }

    private function generateJobId()
    {
        // Format: DEPT-YEAR-SEQ (e.g., ENG-2024-001)
        // For simplicity, we use generic: JOB-YEAR-SEQ
        $year = date('Y');
        $latest = JobPosting::whereYear('created_at', $year)->latest()->first();
        
        if (!$latest) {
             return "JOB-{$year}-001";
        }
        
        // Extract sequence
        // Expected ID: JOB-2024-001
        $parts = explode('-', $latest->job_code);
        $seq = isset($parts[2]) ? (int)$parts[2] : 0;
        $next = str_pad($seq + 1, 3, '0', STR_PAD_LEFT);
        
        return "JOB-{$year}-{$next}";
    }
    public function updateStageConfig(Request $request, JobPosting $job)
    {
        $validated = $request->validate([
            'stage_config' => 'required|array',
        ]);
        
        $job->stage_config = array_merge($job->stage_config ?? [], $validated['stage_config']);
        $job->save();
        
        $this->logger->log('recruitment', 'job_stage_config_update', 'Job Stage Config Updated: ' . $job->job_code, ['job_id' => $job->id, 'config_keys' => array_keys($validated['stage_config'])]);

        return back()->with('success', 'Stage configuration updated.');
    }

    public function bulkUpdateStageConfig(Request $request)
    {
        $validated = $request->validate([
            'job_ids' => 'required|array',
            'job_ids.*' => 'exists:job_postings,id',
            'stage_config' => 'required|array',
        ]);

        $count = 0;
        DB::transaction(function () use ($validated, &$count) {
            $jobs = JobPosting::whereIn('id', $validated['job_ids'])->get();
            foreach ($jobs as $job) {
                $job->stage_config = array_merge($job->stage_config ?? [], $validated['stage_config']);
                $job->save();
                $count++;
            }
            $this->logger->log('recruitment', 'job_stage_config_bulk_update', "Stage Config Applied to {$count} jobs", ['job_ids' => $validated['job_ids']]);
        });

        return back()->with('success', "Stage configuration applied to {$count} jobs.");
    }
}
