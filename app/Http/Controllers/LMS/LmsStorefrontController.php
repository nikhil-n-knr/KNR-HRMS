<?php

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Controller;
use App\Models\LmsCourse;
use App\Models\LMS\LmsPlan;
use App\Models\LMS\LmsCategory;
use App\Models\LMS\LmsEnrollment;
use App\Models\LMS\LmsCourseProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LmsStorefrontController extends Controller
{
    public function catalog(Request $request)
    {
        $courses = LmsCourse::query()
            ->where('is_published', true)
            ->where('is_public', true)
            ->with(['category', 'creator'])
            ->when($request->category, function($q) use ($request) {
                return $q->whereHas('category', fn($cq) => $cq->where('slug', $request->category));
            })
            ->when($request->search, function($q) use ($request) {
                return $q->where(function($sq) use ($request) {
                    $sq->where('title', 'like', "%{$request->search}%")
                       ->orWhere('description', 'like', "%{$request->search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = LmsCategory::whereNull('parent_id')->with('children')->get();

        return Inertia::render('LMS/Public/Catalog', [
            'courses'    => $courses,
            'categories' => $categories,
            'filters'    => $request->only(['search', 'category']),
        ]);
    }

    public function courseShow(LmsCourse $course)
    {
        $course->load(['category', 'creator', 'modules.chapters.concepts']);
        
        return Inertia::render('LMS/Public/CourseShow', [
            'course' => $course,
            'plans'  => LmsPlan::active()->get(),
        ]);
    }

    public function pricing()
    {
        return Inertia::render('LMS/Public/Pricing', [
            'plans' => LmsPlan::active()->get(),
        ]);
    }

    public function checkout(LmsCourse $course)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to initiate mastery.');
        }

        $existing = LmsEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$existing) {
            $enrollment = LmsEnrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'status' => 'active',
                'enrolled_at' => now(),
                'source' => 'self',
            ]);

            LmsCourseProgress::create([
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'course_id' => $course->id,
                'completion_pct' => 0,
                'is_completed' => false,
                'last_activity_at' => now(),
            ]);
        }

        return redirect()->route('lms.learn.hub')->with('success', 'Successfully enrolled into ' . $course->title . '. Prepare for immersion.');
    }
}
