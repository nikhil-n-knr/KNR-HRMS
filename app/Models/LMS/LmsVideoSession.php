<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsVideoSession extends Model
{
    protected $table = 'lms_video_sessions';

    protected $fillable = [
        'video_lesson_id', 'activity_id', 'user_id', 'enrollment_id',
        'total_watch_seconds', 'max_position_seconds', 'completion_pct',
        'is_completed', 'checkpoints_passed', 'watch_segments',
        'popup_checks_passed', 'completed_at', 'last_watched_at',
        'last_position_seconds',
    ];

    protected $casts = [
        'checkpoints_passed' => 'array',
        'watch_segments'     => 'array',
        'is_completed'       => 'boolean',
        'completed_at'       => 'datetime',
        'last_watched_at'    => 'datetime',
    ];

    public function videoLesson() { return $this->belongsTo(LmsVideoLesson::class); }
    public function user()        { return $this->belongsTo(\App\Models\User::class); }
    public function enrollment()  { return $this->belongsTo(LmsEnrollment::class); }

    /**
     * Ingest a heartbeat update from the player frontend.
     * Called every N seconds with current position, watched segments.
     */
    public function recordHeartbeat(int $currentPosition, array $segment, int $watchedSeconds): void
    {
        $segments = $this->watch_segments ?? [];
        $segments[] = $segment; // [start, end] pair

        // Merge overlapping segments for accurate unique watch time
        $mergedSeconds = $this->computeUniqueWatchSeconds($segments);

        $completionPct = $this->videoLesson->duration_seconds > 0
            ? min(100, ($currentPosition / $this->videoLesson->duration_seconds) * 100)
            : 0;

        $required = $this->videoLesson->getRequiredWatchSeconds();
        $isCompleted = $mergedSeconds >= $required
            && $currentPosition >= ($this->videoLesson->duration_seconds - 10); // reached last 10s

        $this->update([
            'total_watch_seconds'  => $mergedSeconds,
            'max_position_seconds' => max($this->max_position_seconds, $currentPosition),
            'completion_pct'       => round($completionPct, 2),
            'watch_segments'       => $segments,
            'last_watched_at'      => now(),
            'last_position_seconds'=> $currentPosition,
            'is_completed'         => $isCompleted,
            'completed_at'         => $isCompleted && !$this->completed_at ? now() : $this->completed_at,
        ]);
    }

    /**
     * Merge overlapping [start, end] segments and compute total unique seconds watched
     */
    private function computeUniqueWatchSeconds(array $segments): int
    {
        if (empty($segments)) return 0;

        usort($segments, fn($a, $b) => $a[0] <=> $b[0]);

        $merged = [];
        $current = $segments[0];

        foreach (array_slice($segments, 1) as $seg) {
            if ($seg[0] <= $current[1]) {
                $current[1] = max($current[1], $seg[1]);
            } else {
                $merged[] = $current;
                $current  = $seg;
            }
        }
        $merged[] = $current;

        return (int) array_sum(array_map(fn($s) => $s[1] - $s[0], $merged));
    }

    /**
     * Record a checkpoint pass
     */
    public function passCheckpoint(int $checkpointId): void
    {
        $passed = $this->checkpoints_passed ?? [];
        if (!in_array($checkpointId, $passed)) {
            $passed[] = $checkpointId;
            $this->update(['checkpoints_passed' => $passed]);
        }
    }
}
