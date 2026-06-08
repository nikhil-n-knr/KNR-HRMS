<?php

namespace App\Http\Controllers\HR\LMS;

use App\Http\Controllers\Controller;
use App\Models\LmsCourse;
use App\Services\LMS\CourseAssignmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    public function __construct(
        private CourseAssignmentService $assignmentService
    ) {}
    
    /**
     * Display course list
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', LmsCourse::class);
        
        $query = LmsCourse::with(['creator', 'assignments', 'certificates'])
            ->withCount(['assignments', 'questions'])
            ->whereNull('institution_id'); // HR-LMS only — Advanced LMS courses have institution_id set
            
        // Search
        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        
        // Filter by category
        if ($request->category) {
            $query->where('category', $request->category);
        }
        
        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $courses = $query->latest()->paginate(20);
        
        return Inertia::render('HR/LMS/Index', [
            'courses' => $courses,
            'filters' => $request->only(['search', 'category', 'is_active'])
        ]);
    }
    
    /**
     * Show course creation wizard
     */
    public function create()
    {
        $this->authorize('create', LmsCourse::class);
        
        return Inertia::render('HR/LMS/Create', [
            'departments' => \App\Models\Department::all(),
            'locations' => \App\Models\Location::all(),
            'roles' => \App\Models\Role::all()
        ]);
    }
    
    /**
     * Store new course
     */
    public function store(Request $request)
    {
        $this->authorize('create', LmsCourse::class);
        
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            
            // Compliance
            'validity_days' => 'required|integer|min:1',
            'target_audience_type' => 'required|in:all,department,gender,role,location',
            'target_audience_config' => 'nullable|array',
            'deadline_days_from_joining' => 'nullable|integer',
            
            // Assessment
            'passing_score' => 'required|numeric|min:0|max:100',
            'max_attempts' => 'required|integer|min:1|max:10',
            'timer_minutes' => 'nullable|integer',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'question_pool_size' => 'nullable|integer',
            
            // Certificate
            'auto_generate_certificate' => 'boolean',
            
            // Restrictions
            'disable_seeking' => 'boolean',
            'min_time_per_section' => 'nullable|integer',
            'prevent_copy_paste' => 'boolean',
            'track_tab_switches' => 'boolean',
            'max_tab_switches' => 'nullable|integer',
            'action_on_fail' => 'required|in:lock,cooloff,notify',
            'cooloff_hours' => 'nullable|integer',
            
            // Content
            'contents' => 'required|array|min:1',
            'contents.*.type' => 'required|in:video,pdf,text,assessment',
            'contents.*.title' => 'required|string',
            'contents.*.file' => 'nullable|file',
            'contents.*.content' => 'nullable|string',
            
            // Questions
            'questions' => 'nullable|array',
            'questions.*.type' => 'required|in:mcq,multi_select,text,scenario,image_based',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                try {
                    $validated['thumbnail_path'] = $request->file('thumbnail')
                        ->store('lms/thumbnails', 'public');
                } catch (\Exception $e) {
                    throw new \Exception('Thumbnail upload failed: ' . $e->getMessage());
                }
            }
            
            $validated['created_by'] = auth()->id();
            
            $course = LmsCourse::create($validated);
        
        // Create course content
        foreach ($validated['contents'] as $index => $content) {
            $contentData = [
                'course_id' => $course->id,
                'order' => $index,
                'type' => $content['type'],
                'title' => $content['title'],
                'description' => $content['description'] ?? null,
                'is_mandatory' => $content['is_mandatory'] ?? true,
            ];
            
            // Handle file uploads
            if (isset($content['file'])) {
                try {
                    $contentData['file_path'] = $content['file']
                        ->store("lms/courses/{$course->id}/content", 'public');
                } catch (\Exception $e) {
                    Log::error('Content file upload failed', [
                        'course_id' => $course->id,
                        'content_index' => $index,
                        'error' => $e->getMessage()
                    ]);
                    // Continue without file
                }
            }
            
            if (isset($content['content'])) {
                $contentData['content'] = $content['content'];
            }
            
            $course->contents()->create($contentData);
        }
        
        // Create questions
        if (isset($validated['questions'])) {
            foreach ($validated['questions'] as $question) {
                $course->questions()->create($question);
            }
        }
        
            DB::commit();
            
            // Auto-assign if configured (outside transaction)
            if ($request->auto_assign) {
                $this->assignmentService->autoAssign($course);
            }
            
            return to_route('hr.lms.index')
                ->with('success', 'Course created successfully')
                ->setStatusCode(303);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded files
            if (isset($validated['thumbnail_path'])) {
                Storage::disk('public')->delete($validated['thumbnail_path']);
            }
            
            Log::error('Course creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->withErrors([
                'error' => 'Failed to create course: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Show course details
     */
    public function show(LmsCourse $course)
    {
        $this->authorize('view', $course);
        
        $course->load(['contents', 'questions', 'assignments.employee', 'certificates']);
        
        return Inertia::render('HR/LMS/Show', [
            'course' => $course
        ]);
    }
    
    /**
     * Edit course
     */
    public function edit(LmsCourse $course)
    {
        $this->authorize('update', $course);
        
        $course->load(['contents', 'questions']);
        
        return Inertia::render('HR/LMS/Edit', [
            'course' => $course,
            'departments' => \App\Models\Department::all(),
            'locations' => \App\Models\Location::all(),
            'roles' => \App\Models\Role::all()
        ]);
    }
    
    /**
     * Update course
     */
    public function update(Request $request, LmsCourse $course)
    {
        $this->authorize('update', $course);
        
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'is_active' => 'boolean',
            
            // Compliance
            'validity_days' => 'required|integer|min:1',
            'target_audience_type' => 'required|in:all,department,gender,role,location',
            'target_audience_config' => 'nullable|array',
            'deadline_days_from_joining' => 'nullable|integer',
            
            // Assessment
            'passing_score' => 'required|numeric|min:0|max:100',
            'max_attempts' => 'required|integer|min:1|max:10',
            'timer_minutes' => 'nullable|integer',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'question_pool_size' => 'nullable|integer',
            
            // Certificate
            'auto_generate_certificate' => 'boolean',
            
            // Restrictions
            'disable_seeking' => 'boolean',
            'min_time_per_section' => 'nullable|integer',
            'prevent_copy_paste' => 'boolean',
            'track_tab_switches' => 'boolean',
            'max_tab_switches' => 'nullable|integer',
            'action_on_fail' => 'required|in:lock,cooloff,notify',
            'cooloff_hours' => 'nullable|integer',
        ]);
        
        $course->update($validated);
        
        return to_route('hr.lms.show', $course)
            ->with('success', 'Course updated successfully')
            ->setStatusCode(303);
    }
    
    /**
     * Delete course
     */
    public function destroy(LmsCourse $course)
    {
        $this->authorize('delete', $course);
        
        // Delete associated files
        if ($course->thumbnail_path) {
            Storage::disk('public')->delete($course->thumbnail_path);
        }
        
        foreach ($course->contents as $content) {
            if ($content->file_path) {
                Storage::disk('public')->delete($content->file_path);
            }
        }
        
        $course->delete();
        
        return to_route('hr.lms.index')
            ->with('success', 'Course deleted successfully')
            ->setStatusCode(303);
    }
    
    /**
     * Assign course to employees
     */
    public function assign(Request $request, LmsCourse $course)
    {
        $this->authorize('update', $course);
        
        if ($request->auto_assign) {
            $result = $this->assignmentService->autoAssign($course);
            
            return back()->with('success', 
                "Course assigned to {$result['assigned']} employees. {$result['skipped']} skipped."
            );
        }
        
        // Manual assignment
        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'due_days' => 'nullable|integer|min:1|max:365'
        ]);
        
        $assigned = 0;
        $skipped = 0;
        $dueDays = $validated['due_days'] ?? 30;
        
        foreach ($validated['employee_ids'] as $employeeId) {
            // Check if already assigned
            $existing = \App\Models\LmsAssignment::where([
                'course_id' => $course->id,
                'employee_id' => $employeeId
            ])->whereIn('status', ['pending', 'in_progress'])->exists();
            
            if ($existing) {
                $skipped++;
                continue;
            }
            
            $dueDate = now()->addDays($dueDays);
            $validUntil = $dueDate->copy()->addDays($course->validity_days);
            
            \App\Models\LmsAssignment::create([
                'course_id' => $course->id,
                'employee_id' => $employeeId,
                'assigned_on' => now(),
                'due_date' => $dueDate,
                'valid_until' => $validUntil,
                'assigned_by' => auth()->id(),
                'status' => 'pending'
            ]);
            
            $assigned++;
        }
        
        return back()->with('success', "Course assigned to {$assigned} employees. {$skipped} skipped.");
    }
}
