<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsVideoLesson extends Model
{
    protected $table = 'lms_video_lessons';

    protected $fillable = [
        'activity_id', 'source_type', 'video_url', 'video_path',
        'thumbnail_path', 'duration_seconds',
        'min_watch_pct', 'disable_seeking', 'disable_fast_forward',
        'seek_buffer_seconds', 'random_check_popup',
        'check_popup_interval_minutes', 'checkpoints',
        'allow_notes', 'subtitles', 'transcript_path',
    ];

    protected $casts = [
        'checkpoints'          => 'array',
        'subtitles'            => 'array',
        'disable_seeking'      => 'boolean',
        'disable_fast_forward' => 'boolean',
        'random_check_popup'   => 'boolean',
        'allow_notes'          => 'boolean',
    ];

    public function activity() { return $this->belongsTo(LmsActivity::class, 'activity_id'); }

    public function watchSessions()
    {
        return $this->hasMany(LmsVideoSession::class, 'video_lesson_id');
    }

    public function getWatchSessionForUser(int $userId): ?LmsVideoSession
    {
        return $this->watchSessions()->where('user_id', $userId)->first();
    }

    /**
     * Check if a user has satisfied this video's completion criteria
     */
    public function isCompletedByUser(int $userId): bool
    {
        $session = $this->getWatchSessionForUser($userId);
        return $session && $session->is_completed;
    }

    /**
     * Get required watch seconds from percentage
     */
    public function getRequiredWatchSeconds(): int
    {
        return (int) ceil($this->duration_seconds * ($this->min_watch_pct / 100));
    }
}
