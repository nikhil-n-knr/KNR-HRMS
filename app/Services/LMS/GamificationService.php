<?php

namespace App\Services\LMS;

use App\Models\LMS\LmsEnrollment;
use App\Models\LMS\LmsActivity;
use App\Models\LMS\LmsUserPoints;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Gamification & Rewards Service
 * 
 * Manages the points economy for the advanced LMS.
 * Credits learners for concepts, modules, courses, and quiz results.
 */
class GamificationService
{
    /**
     * Credit points for completing an activity.
     */
    public function creditActivityPoints(LmsActivity $activity, User $user)
    {
        $points = $activity->points ?? 10;
        
        $this->awardPoints($user, 'activity_completion', $points, [
            'activity_id' => $activity->id,
            'title'       => $activity->title
        ]);
    }

    /**
     * Credit bonus points based on quiz performance.
     */
    public function creditQuizPoints($attempt, User $user)
    {
        $pct = $attempt->score_obtained / $attempt->max_score * 100;
        $bonus = ($pct >= 90) ? 50 : (($pct >= 75) ? 20 : 0);

        if ($bonus > 0) {
            $this->awardPoints($user, 'quiz_performance', $bonus, [
                'attempt_id' => $attempt->id,
                'score'      => $pct
            ]);
        }
    }

    /**
     * Credit major milestone points for course completion.
     */
    public function creditCoursePoints(LmsEnrollment $enrollment)
    {
        $points = 500; // Major completion reward
        
        $this->awardPoints($enrollment->user, 'course_completion', $points, [
            'course_id' => $enrollment->course_id,
            'title'     => $enrollment->course->title
        ]);
    }

    /**
     * Get Leaderboard for an institution.
     */
    public function getInstitutionLeaderboard($institutionId, $period = 'all')
    {
        $query = LmsUserPoints::select('user_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit(10);

        if ($period === 'monthly') {
            $query->whereMonth('created_at', now()->month);
        }

        return $query->with('user:id,name')->get();
    }

    /**
     * Internal: Record point award entry.
     */
    protected function awardPoints(User $user, $action, $points, $metadata = [])
    {
        return LmsUserPoints::create([
            'user_id' => $user->id,
            'course_id' => $metadata['course_id'] ?? null,
            'action' => $action,
            'points' => $points,
            'metadata' => $metadata,
            'description' => $metadata['title'] ?? $action
        ]);
    }
}
