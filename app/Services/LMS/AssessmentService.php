<?php

namespace App\Services\LMS;

use App\Models\LmsAttempt;
use App\Models\LmsCourse;
use App\Models\LmsQuestion;
use App\Models\LmsAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssessmentService
{
    /**
     * Generate shuffled question set for exam
     */
    public function generateQuestionSet(LmsCourse $course): array
    {
        $questions = $course->questions()->where('is_active', true)->get();
        
        // If pool size configured, randomly pick
        if ($course->question_pool_size && $questions->count() > $course->question_pool_size) {
            $questions = $questions->random($course->question_pool_size);
        }
        
        // Shuffle questions if configured
        if ($course->shuffle_questions) {
            $questions = $questions->shuffle();
        }
        
        return $questions->map(function($q) use ($course) {
            $options = $q->options;
            
            // Shuffle options if configured
            if ($course->shuffle_options && $q->type !== 'text') {
                shuffle($options);
            }
            
            // Remove correct answer flags before sending to frontend
            $sanitizedOptions = collect($options)->map(function($opt) {
                return [
                    'id' => $opt['id'] ?? uniqid(),
                    'text' => $opt['text'],
                    // Don't expose is_correct to frontend
                ];
            })->toArray();
            
            return [
                'id' => $q->id,
                'type' => $q->type,
                'question_text' => $q->question_text,
                'scenario_context' => $q->scenario_context,
                'image_url' => $q->image_path ? asset('storage/' . $q->image_path) : null,
                'options' => $sanitizedOptions,
                'score_weight' => $q->score_weight
            ];
        })->toArray();
    }
    
    /**
     * Start a new attempt
     */
    public function startAttempt(LmsAssignment $assignment): LmsAttempt
    {
        $attemptNumber = $assignment->attempts()->count() + 1;
        
        $attempt = LmsAttempt::create([
            'assignment_id' => $assignment->id,
            'employee_id' => $assignment->employee_id,
            'course_id' => $assignment->course_id,
            'attempt_number' => $attemptNumber,
            'started_at' => now(),
            'answers_log' => [],
            'status' => 'in_progress'
        ]);
        
        // Update assignment status
        if ($assignment->status === 'pending') {
            $assignment->update(['status' => 'in_progress']);
        }
        
        Log::info('Attempt started', [
            'attempt_id' => $attempt->id,
            'employee_id' => $assignment->employee_id,
            'attempt_number' => $attemptNumber
        ]);
        
        return $attempt;
    }
    
    /**
     * Grade attempt
     */
    public function gradeAttempt(LmsAttempt $attempt): void
    {
        Log::context(['attempt_id' => $attempt->id]);
        
        $course = $attempt->course;
        $answersLog = $attempt->answers_log;
        
        $totalScore = 0;
        $maxScore = 0;
        
        foreach ($answersLog as $questionId => $answer) {
            $question = LmsQuestion::find($questionId);
            if (!$question) continue;
            
            $maxScore += $question->max_score;
            
            // Skip manual grading questions
            if ($question->require_manual_grading) {
                continue;
            }
            
            $score = $this->calculateQuestionScore($question, $answer);
            $totalScore += $score;
        }
        
        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
        $isPassed = $percentage >= $course->passing_score;
        
        $timeSpent = now()->diffInSeconds($attempt->started_at);
        
        $attempt->update([
            'score_obtained' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => round($percentage, 2),
            'is_passed' => $isPassed,
            'submitted_at' => now(),
            'time_spent_seconds' => $timeSpent,
            'status' => 'submitted'
        ]);
        
        Log::info('Attempt graded', [
            'score' => $totalScore,
            'max' => $maxScore,
            'percentage' => $percentage,
            'passed' => $isPassed
        ]);
        
        // Update assignment status
        if ($isPassed) {
            $attempt->assignment->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
        }
    }
    
    /**
     * Calculate score for individual question
     */
    private function calculateQuestionScore($question, $answer): float
    {
        switch ($question->type) {
            case 'mcq':
                $correctOption = collect($question->options)
                    ->firstWhere('is_correct', true);
                    
                if ($correctOption && $answer['selected'] === $correctOption['id']) {
                    return $question->max_score;
                }
                return 0;
                
            case 'multi_select':
                $correctIds = collect($question->options)
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->toArray();
                    
                $selectedIds = $answer['selected'] ?? [];
                
                // Partial scoring: correct selections / total correct
                $correctSelections = count(array_intersect($selectedIds, $correctIds));
                $incorrectSelections = count(array_diff($selectedIds, $correctIds));
                
                if ($incorrectSelections > 0) {
                    return 0; // Penalty for wrong selections
                }
                
                return ($correctSelections / count($correctIds)) * $question->max_score;
                
            case 'scenario':
            case 'image_based':
                // These use weighted scoring from options
                $selectedOption = collect($question->options)
                    ->firstWhere('id', $answer['selected']);
                    
                return $selectedOption['score'] ?? 0;
                
            default:
                return 0;
        }
    }
    
    /**
     * Get analytics for a question
     */
    public function getQuestionAnalytics(int $questionId): array
    {
        $question = LmsQuestion::findOrFail($questionId);
        
        // Get all attempts that answered this question
        $attempts = LmsAttempt::where('course_id', $question->course_id)
            ->where('status', 'submitted')
            ->get();
            
        $totalAttempts = 0;
        $correctAnswers = 0;
        $optionStats = [];
        
        foreach ($attempts as $attempt) {
            $answer = $attempt->answers_log[$questionId] ?? null;
            if (!$answer) continue;
            
            $totalAttempts++;
            
            $score = $this->calculateQuestionScore($question, $answer);
            if ($score >= $question->max_score) {
                $correctAnswers++;
            }
            
            // Track option selection frequency
            $selected = $answer['selected'];
            if (!isset($optionStats[$selected])) {
                $optionStats[$selected] = 0;
            }
            $optionStats[$selected]++;
        }
        
        $successRate = $totalAttempts > 0 ? ($correctAnswers / $totalAttempts) * 100 : 0;
        
        return [
            'question_id' => $questionId,
            'total_attempts' => $totalAttempts,
            'correct_answers' => $correctAnswers,
            'success_rate' => round($successRate, 2),
            'option_stats' => $optionStats,
            'difficulty' => $this->calculateDifficulty($successRate)
        ];
    }
    
    /**
     * Generate random test from question pool
     */
    public function generateRandomTest(LmsCourse $course, ?int $poolSize = null): Collection
    {
        $poolSize = $poolSize ?? $course->question_pool_size ?? $course->questions()->count();
        
        if ($poolSize === 0) {
            return collect();
        }
        
        // Calculate difficulty distribution (30% easy, 50% medium, 20% hard)
        $easyCount = max(1, (int)($poolSize * 0.3));
        $mediumCount = max(1, (int)($poolSize * 0.5));
        $hardCount = max(1, (int)($poolSize * 0.2));
        
        // Adjust if total doesn't match pool size
        $total = $easyCount + $mediumCount + $hardCount;
        if ($total < $poolSize) {
            $mediumCount += ($poolSize - $total);
        } elseif ($total > $poolSize) {
            $mediumCount -= ($total - $poolSize);
        }
        
        $questions = collect();
        
        // Get easy questions
        $easy = $course->questions()
            ->where('difficulty', 'easy')
            ->inRandomOrder()
            ->limit($easyCount)
            ->get();
        $questions = $questions->merge($easy);
        
        // Get medium questions
        $medium = $course->questions()
            ->where('difficulty', 'medium')
            ->inRandomOrder()
            ->limit($mediumCount)
            ->get();
        $questions = $questions->merge($medium);
        
        // Get hard questions
        $hard = $course->questions()
            ->where('difficulty', 'hard')
            ->inRandomOrder()
            ->limit($hardCount)
            ->get();
        $questions = $questions->merge($hard);
        
        // If we don't have enough questions with difficulty tags, fill with random
        if ($questions->count() < $poolSize) {
            $remaining = $course->questions()
                ->whereNotIn('id', $questions->pluck('id'))
                ->inRandomOrder()
                ->limit($poolSize - $questions->count())
                ->get();
            $questions = $questions->merge($remaining);
        }
        
        // Shuffle final set
        return $questions->shuffle()->take($poolSize);
    }
    
    /**
     * Calculate question difficulty based on success rate
     */
    public function calculateQuestionDifficulty(LmsQuestion $question): string
    {
        $attempts = LmsAttempt::whereHas('answers', function($q) use ($question) {
            $q->where('question_id', $question->id);
        })->where('status', 'completed')->get();
        
        if ($attempts->count() < 5) {
            return 'medium'; // Not enough data
        }
        
        $correctCount = $attempts->filter(function($attempt) use ($question) {
            $answer = $attempt->answers->where('question_id', $question->id)->first();
            return $answer && $answer->is_correct;
        })->count();
        
        $successRate = $correctCount / $attempts->count();
        
        if ($successRate > 0.75) {
            return 'easy';
        } elseif ($successRate > 0.40) {
            return 'medium';
        } else {
            return 'hard';
        }
    }
}
