<?php

namespace App\Notifications\Project;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use App\Models\ProjectStage;

class TaskMovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $oldStage;
    protected $newStage;
    protected $mover; // User who moved the task

    public function __construct(Task $task, ProjectStage $oldStage, ProjectStage $newStage, $mover)
    {
        $this->task = $task;
        $this->oldStage = $oldStage;
        $this->newStage = $newStage;
        $this->mover = $mover;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'task_moved',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'old_stage' => $this->oldStage->name,
            'new_stage' => $this->newStage->name,
            'moved_by' => $this->mover->name,
            'project_id' => $this->task->project_id,
            'message' => "Task '{$this->task->title}' moved to {$this->newStage->name} by {$this->mover->name}."
        ];
    }
}
