<?php

namespace App\Http\Controllers\HR\LMS;

use App\Http\Controllers\Controller;
use App\Models\LmsQuestion;
use App\Models\LmsCourse;
use App\Services\LMS\AssessmentService;
use App\Services\LMS\QuestionImportService; // Added
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse; // Added

class QuestionController extends Controller
{
    public function __construct(
        private AssessmentService $assessmentService,
        private QuestionImportService $importService // Added
    ) {}
    
    /**
     * Show question bank
     */
    public function index(Request $request)
    {
        $query = LmsQuestion::with('course');
        
        // Filter by course
        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }
        
        // Filter by type
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        // Search
        if ($request->search) {
            $query->where('question_text', 'like', "%{$request->search}%");
        }
        
        $questions = $query->latest()->paginate(20);
        
        return Inertia::render('HR/LMS/QuestionBank', [
            'questions' => $questions,
            'courses' => LmsCourse::select('id', 'title')->get(),
            'filters' => $request->only(['course_id', 'type', 'search'])
        ]);
    }
    
    /**
     * Store new question
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:lms_courses,id',
            'type' => 'required|in:mcq,multi_select,text,scenario,image_based',
            'question_text' => 'required|string',
            'scenario_context' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'boolean',
            'options.*.score' => 'nullable|integer',
            'score_weight' => 'integer|min:1',
            'max_score' => 'required|integer|min:1',
            'explanation' => 'nullable|string',
            'require_manual_grading' => 'boolean'
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')
                ->store('lms/questions', 'public');
        }
        
        // Add unique IDs to options
        $validated['options'] = collect($validated['options'])->map(function($opt) {
            $opt['id'] = uniqid();
            return $opt;
        })->toArray();
        
        $question = LmsQuestion::create($validated);
        
        return back()->with('success', 'Question added successfully');
    }
    
    /**
     * Update question
     */
    public function update(Request $request, LmsQuestion $question)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'options' => 'required|array',
            'is_active' => 'boolean'
        ]);
        
        $question->update($validated);
        
        return back()->with('success', 'Question updated successfully');
    }
    
    /**
     * Delete question
     */
    public function destroy(LmsQuestion $question)
    {
        if ($question->image_path) {
            Storage::disk('public')->delete($question->image_path);
        }
        
        $question->delete();
        
        return back()->with('success', 'Question deleted successfully');
    }
    
    /**
     * Get question analytics
     */
    public function analytics(LmsQuestion $question)
    {
        $analytics = $this->assessmentService->getQuestionAnalytics($question->id);
        
        return response()->json($analytics);
    }
    
    /**
     * Bulk import questions from CSV
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:lms_courses,id',
            'file' => 'required|file|mimes:csv,txt|max:5120'
        ]);
        
        try {
            $results = $this->importService->importFromCsv(
                $request->file('file'),
                $request->course_id
            );
            
            $message = "Import complete: {$results['success']} successful, {$results['failed']} failed";
            
            return back()->with([
                'success' => $message,
                'import_results' => $results
            ]);
            
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Download CSV template
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="question_import_template.csv"'
        ];
        
        return response()->stream(function() {
            echo $this->importService->generateTemplate();
        }, 200, $headers);
    }
}
