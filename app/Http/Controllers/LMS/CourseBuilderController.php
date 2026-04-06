<?php

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Controller;
use App\Models\LMS\LmsInstitution;
use App\Models\LMS\LmsProgram;
use App\Models\LMS\LmsCategory;
use App\Models\LMS\LmsModule;
use App\Models\LMS\LmsChapter;
use App\Models\LMS\LmsConcept;
use App\Models\LMS\LmsActivity;
use App\Models\LMS\LmsVideoLesson;
use App\Models\LMS\LmsReadingMaterial;
use App\Models\LMS\LmsQuizConfig;
use App\Models\LMS\LmsAssignmentV2;
use App\Models\LMS\LmsLiveSession;
use App\Models\LMS\LmsEnrollment;
use App\Models\LMS\LmsCourseProgress;
use App\Models\LMS\LmsCertificateRule;
use App\Models\LMS\LmsCertificateTemplate;
use App\Models\LmsCourse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CourseBuilderController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // COURSE LISTING & MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = LmsCourse::with(['category', 'institution', 'program', 'creator'])
            ->withCount(['modules', 'enrollments'])
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->institution_id, fn($q) => $q->where('institution_id', $request->institution_id))
            ->when($request->status === 'published', fn($q) => $q->where('is_published', true))
            ->when($request->status === 'draft', fn($q) => $q->where('is_published', false))
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_dir ?? 'desc');

        $categories   = LmsCategory::active()->roots()->with('allChildren')->get();
        $institutions = LmsInstitution::active()->roots()->with('allChildren')->get();

        return Inertia::render('LMS/Admin/Courses/Index', [
            'courses'      => $query->paginate(15)->withQueryString(),
            'categories'   => $this->buildCategoryTree($categories),
            'institutions' => $this->buildInstitutionTree($institutions),
            'filters'      => $request->only(['search', 'category_id', 'institution_id', 'status']),
            'stats' => [
                'total'     => LmsCourse::count(),
                'published' => LmsCourse::where('is_published', true)->count(),
                'draft'     => LmsCourse::where('is_published', false)->count(),
                'enrolled'  => LmsEnrollment::distinct('user_id')->count('user_id'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('LMS/Admin/Courses/Builder', [
            'mode'         => 'create',
            'categories'   => $this->buildCategoryTree(LmsCategory::active()->roots()->with('allChildren')->get()),
            'institutions' => LmsInstitution::active()->orderBy('name')->get(),
            'programs'     => LmsProgram::with('institution')->orderBy('name')->get(),
            'templates'    => LmsCertificateTemplate::active()->get(),
            'rules'        => LmsCertificateRule::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'required|string',
            'category_id'        => 'nullable|exists:lms_categories,id',
            'institution_id'     => 'nullable|exists:lms_institutions,id',
            'program_id'         => 'nullable|exists:lms_programs,id',
            'semester'           => 'nullable|integer|min:1|max:12',
            'level'              => 'nullable|in:beginner,intermediate,advanced',
            'language'           => 'nullable|string|max:10',
            'mode'               => 'nullable|in:standalone,institutional,both',
            'is_public'          => 'boolean',
            'allow_self_enrollment' => 'boolean',
            'tags'               => 'nullable|array',
            'what_youll_learn'   => 'nullable|string',
            'course_requirements'=> 'nullable|string',
            'target_audience'    => 'nullable|string',
            'certificate_rule_id'     => 'nullable|exists:lms_certificate_rules,id',
            'certificate_template_id' => 'nullable|exists:lms_certificate_templates,id',
        ]);

        $course = LmsCourse::create(array_merge($validated, [
            'created_by'  => auth()->id(),
            'slug'        => Str::slug($validated['title']),
            'is_published'=> false,
            'is_active'   => true,
        ]));

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('lms/thumbnails', 'public');
            $course->update(['thumbnail_path' => $path]);
        }

        return redirect()->route('lms.admin.courses.builder', $course->id)
            ->with('success', 'Course created! Now add modules and content.');
    }

    public function show(LmsCourse $course)
    {
        $course->load([
            'modules.chapters.concepts.activities.videoLesson',
            'modules.chapters.concepts.activities.quizConfig',
            'modules.chapters.concepts.activities.assignment',
            'modules.chapters.concepts.activities.liveSession',
            'category',
            'institution',
            'program',
            'creator',
        ]);

        $stats = [
            'enrolled_count'   => LmsEnrollment::where('course_id', $course->id)->count(),
            'completed_count'  => LmsCourseProgress::where('course_id', $course->id)->where('is_completed', true)->count(),
            'avg_completion'   => LmsCourseProgress::where('course_id', $course->id)->avg('completion_pct'),
            'avg_quiz_score'   => LmsCourseProgress::where('course_id', $course->id)->avg('avg_quiz_score'),
            'total_watch_hours'=> DB::table('lms_course_progress')->where('course_id', $course->id)->sum('total_watch_seconds') / 3600,
            'live_sessions'    => LmsLiveSession::where('course_id', $course->id)->count(),
        ];

        return Inertia::render('LMS/Admin/Courses/Show', [
            'course'     => $course,
            'stats'      => $stats,
            'categories' => $this->buildCategoryTree(LmsCategory::active()->roots()->with('allChildren')->get()),
        ]);
    }

    public function builder(LmsCourse $course)
    {
        $course->load([
            'modules' => fn($q) => $q->ordered()->with([
                'chapters' => fn($q) => $q->ordered()->with([
                    'concepts' => fn($q) => $q->ordered()->with([
                        'activities' => fn($q) => $q->ordered()->with([
                            'videoLesson', 'readingMaterial', 'quizConfig', 'assignment', 'liveSession'
                        ])
                    ])
                ])
            ])
        ]);

        return Inertia::render('LMS/Admin/Courses/Builder', [
            'mode'         => 'edit',
            'course'       => $course,
            'categories'   => $this->buildCategoryTree(LmsCategory::active()->roots()->with('allChildren')->get()),
            'institutions' => LmsInstitution::active()->orderBy('name')->get(),
            'programs'     => LmsProgram::with('institution')->orderBy('name')->get(),
            'templates'    => LmsCertificateTemplate::where('is_active', true)->get(),
            'rules'        => LmsCertificateRule::all(),
        ]);
    }

    public function update(Request $request, LmsCourse $course)
    {
        $validated = $request->validate([
            'title'          => 'sometimes|string|max:255',
            'description'    => 'sometimes|string',
            'category_id'    => 'nullable|exists:lms_categories,id',
            'institution_id' => 'nullable|exists:lms_institutions,id',
            'program_id'     => 'nullable|exists:lms_programs,id',
            'semester'       => 'nullable|integer',
            'level'          => 'nullable|in:beginner,intermediate,advanced',
            'language'       => 'nullable|string',
            'mode'           => 'nullable|in:standalone,institutional,both',
            'is_public'      => 'boolean',
            'is_published'   => 'boolean',
            'tags'           => 'nullable|array',
            'what_youll_learn'    => 'nullable|string',
            'course_requirements' => 'nullable|string',
            'target_audience'     => 'nullable|string',
            'certificate_rule_id'     => 'nullable|exists:lms_certificate_rules,id',
            'certificate_template_id' => 'nullable|exists:lms_certificate_templates,id',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $course->update($validated);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('lms/thumbnails', 'public');
            $course->update(['thumbnail_path' => $path]);
        }

        return back()->with('success', 'Course updated successfully.');
    }

    public function destroy(LmsCourse $course)
    {
        $course->delete();
        return redirect()->route('lms.admin.courses.index')->with('success', 'Course deleted.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // COURSE STRUCTURE BUILDER (Module / Chapter / Concept / Activity CRUD)
    // ─────────────────────────────────────────────────────────────────────

    public function storeModule(Request $request, LmsCourse $course)
    {
        $data = $request->validate([
            'title'                    => 'required|string|max:255',
            'description'              => 'nullable|string',
            'sort_order'               => 'nullable|integer',
            'is_mandatory'             => 'boolean',
            'estimated_duration_minutes' => 'nullable|integer',
            'has_module_certificate'   => 'boolean',
        ]);

        $module = $course->modules()->create(array_merge($data, ['is_active' => true]));

        // Update course stats
        $course->increment('total_modules');

        return response()->json(['module' => $module->load('chapters')]);
    }

    public function updateModule(Request $request, LmsCourse $course, LmsModule $module)
    {
        $module->update($request->validate([
            'title'                    => 'sometimes|string|max:255',
            'description'              => 'nullable|string',
            'sort_order'               => 'nullable|integer',
            'is_mandatory'             => 'boolean',
            'estimated_duration_minutes' => 'nullable|integer',
            'has_module_certificate'   => 'boolean',
            'is_active'                => 'boolean',
        ]));

        return response()->json(['module' => $module->fresh()]);
    }

    public function destroyModule(LmsCourse $course, LmsModule $module)
    {
        $module->delete();
        $course->decrement('total_modules');
        return response()->json(['ok' => true]);
    }

    public function storeChapter(Request $request, LmsCourse $course, LmsModule $module)
    {
        $data    = $request->validate(['title' => 'required|string', 'description' => 'nullable|string', 'sort_order' => 'nullable|integer', 'is_mandatory' => 'boolean']);
        $chapter = $module->chapters()->create(array_merge($data, ['course_id' => $course->id, 'is_active' => true]));
        return response()->json(['chapter' => $chapter->load('concepts')]);
    }

    public function updateChapter(Request $request, LmsCourse $course, LmsModule $module, LmsChapter $chapter)
    {
        $chapter->update($request->validate(['title' => 'sometimes|string', 'description' => 'nullable|string', 'sort_order' => 'nullable|integer', 'is_mandatory' => 'boolean']));
        return response()->json(['chapter' => $chapter->fresh()]);
    }

    public function storeConcept(Request $request, LmsCourse $course, LmsModule $module, LmsChapter $chapter)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'learning_outcomes'   => 'nullable|array',
            'standard_code'       => 'nullable|string',
            'sort_order'          => 'nullable|integer',
            'is_mandatory'        => 'boolean',
            'estimated_duration_minutes' => 'nullable|integer',
            'min_time_seconds'    => 'nullable|integer',
            'has_micro_quiz'      => 'boolean',
            'has_micro_certificate' => 'boolean',
            'completion_criteria' => 'nullable|array',
        ]);

        $concept = $chapter->concepts()->create(array_merge($data, [
            'module_id' => $module->id,
            'course_id' => $course->id,
            'is_active' => true,
        ]));

        $course->increment('total_concepts');

        return response()->json(['concept' => $concept->load('activities')]);
    }

    public function updateConcept(Request $request, LmsCourse $course, LmsModule $module, LmsChapter $chapter, LmsConcept $concept)
    {
        $concept->update($request->validate([
            'title'               => 'sometimes|string|max:255',
            'description'         => 'nullable|string',
            'learning_outcomes'   => 'nullable|array',
            'sort_order'          => 'nullable|integer',
            'is_mandatory'        => 'boolean',
            'completion_criteria' => 'nullable|array',
        ]));
        return response()->json(['concept' => $concept->fresh()]);
    }

    public function storeActivity(Request $request, LmsCourse $course, LmsConcept $concept)
    {
        $data = $request->validate([
            'type'        => 'required|in:video,reading,quiz,assignment,discussion,live_session',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_mandatory'=> 'boolean',
            'is_graded'   => 'boolean',
            'config'      => 'nullable|array',
        ]);

        $activity = $concept->activities()->create(array_merge($data, [
            'chapter_id' => $concept->chapter_id,
            'module_id'  => $concept->module_id,
            'course_id'  => $course->id,
            'is_active'  => true,
        ]));

        // Create type-specific record
        $detail = $this->createActivityDetail($activity, $request);

        return response()->json(['activity' => $activity->fresh()->load('videoLesson', 'readingMaterial', 'quizConfig', 'assignment', 'liveSession'), 'detail' => $detail]);
    }

    public function updateActivity(Request $request, LmsCourse $course, LmsConcept $concept, LmsActivity $activity)
    {
        $validated = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'sort_order'   => 'nullable|integer',
            'is_mandatory' => 'boolean',
            'is_graded'    => 'boolean',
        ]);

        $activity->update($validated);

        // Update detail based on type
        $this->updateActivityDetail($activity, $request);

        return response()->json(['activity' => $activity->fresh()->load('videoLesson', 'readingMaterial', 'quizConfig', 'assignment', 'liveSession')]);
    }

    private function updateActivityDetail(LmsActivity $activity, Request $request)
    {
        $data = $request->all();
        
        switch ($activity->type) {
            case 'video':
                $activity->videoLesson()->update($request->only(['source_type','video_url','duration_seconds','min_watch_pct','disable_seeking','random_check_popup','checkpoints']));
                break;
            case 'reading':
                $activity->readingMaterial()->update($request->only(['content_type','content','file_path','external_url','estimated_read_minutes']));
                break;
            case 'quiz':
                $activity->quizConfig()->update($request->only(['duration_minutes','max_attempts','pass_mark_pct','shuffle_questions','shuffle_options','question_ids','feedback_mode']));
                break;
            case 'assignment':
                $activity->assignment()->update($request->only(['rubric','max_score','pass_mark','allow_resubmission','max_resubmissions']));
                break;
            case 'live_session':
                $activity->liveSession()->update($request->only(['scheduled_at','duration_minutes','provider','meeting_url','min_attendance_pct','auto_record']));
                break;
        }
    }

    public function destroyActivity(LmsCourse $course, LmsConcept $concept, LmsActivity $activity)
    {
        $activity->delete(); // Detail rows should be deleted by cascade or manually
        return response()->json(['ok' => true]);
    }

    public function reorderStructure(Request $request, LmsCourse $course)
    {
        // Bulk reorder modules/chapters/concepts via drag-drop
        $request->validate(['items' => 'required|array', 'items.*.id' => 'required', 'items.*.sort_order' => 'required|integer', 'type' => 'required|in:module,chapter,concept,activity']);

        $modelMap = [
            'module'   => LmsModule::class,
            'chapter'  => LmsChapter::class,
            'concept'  => LmsConcept::class,
            'activity' => LmsActivity::class,
        ];

        $model = $modelMap[$request->type];
        foreach ($request->items as $item) {
            $model::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['ok' => true]);
    }

    public function publish(LmsCourse $course)
    {
        // Validate minimum structure
        if ($course->modules()->count() === 0) {
            return back()->withErrors(['publish' => 'A course must have at least one module before publishing.']);
        }

        $course->update(['is_published' => true]);
        return back()->with('success', 'Course published successfully!');
    }

    // ─────────────────────────────────────────────────────────────────────
    // BULK ENROLLMENT
    // ─────────────────────────────────────────────────────────────────────

    public function bulkEnroll(Request $request, LmsCourse $course)
    {
        $request->validate([
            'user_ids'       => 'required|array',
            'user_ids.*'     => 'exists:users,id',
            'institution_id' => 'nullable|exists:lms_institutions,id',
            'program_id'     => 'nullable|exists:lms_programs,id',
            'semester'       => 'nullable|integer',
        ]);

        $enrolled = 0;
        foreach ($request->user_ids as $userId) {
            $existing = LmsEnrollment::where(['user_id' => $userId, 'course_id' => $course->id])->exists();
            if (!$existing) {
                LmsEnrollment::create([
                    'user_id'        => $userId,
                    'course_id'      => $course->id,
                    'institution_id' => $request->institution_id,
                    'program_id'     => $request->program_id,
                    'semester'       => $request->semester,
                    'source'         => 'manual',
                    'status'         => 'active',
                    'enrolled_by'    => auth()->id(),
                ]);
                $enrolled++;
            }
        }

        $course->update(['enrolled_count' => LmsEnrollment::where('course_id', $course->id)->count()]);

        return back()->with('success', "{$enrolled} learners enrolled successfully.");
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────

    private function buildCategoryTree($categories): array
    {
        return $categories->map(fn($cat) => [
            'id'       => $cat->id,
            'name'     => $cat->name,
            'slug'     => $cat->slug,
            'depth'    => $cat->depth,
            'children' => $this->buildCategoryTree($cat->children ?? collect()),
        ])->toArray();
    }

    private function buildInstitutionTree($institutions): array
    {
        return $institutions->map(fn($inst) => [
            'id'       => $inst->id,
            'name'     => $inst->name,
            'type'     => $inst->type,
            'children' => $this->buildInstitutionTree($inst->children ?? collect()),
        ])->toArray();
    }
}
