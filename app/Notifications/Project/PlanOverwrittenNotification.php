<?php

namespace App\Notifications\Project;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanOverwrittenNotification extends Notification
{
    use Queueable;

    protected $project;
    protected $task;
    protected $overwriter;
    protected $changes;

    /**
     * Create a new notification instance.
     */
    public function __construct($project, $task, $overwriter, $changes = [])
    {
        $this->project = $project;
        $this->task = $task;
        $this->overwriter = $overwriter;
        $this->changes = $changes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $target = $this->task ? "Task '{$this->task->title}'" : "Project '{$this->project->name}'";
        
        $mail = (new MailMessage)
                    ->subject('CRITICAL: Locked Plan Overwritten')
                    ->greeting('Hello,')
                    ->line("A locked plan for {$target} has been overwritten by {$this->overwriter->name}.")
                    ->line('Changes detected:');

        foreach ($this->changes as $field => $data) {
            $old = is_array($data['old']) ? json_encode($data['old']) : $data['old'];
            $new = is_array($data['new']) ? json_encode($data['new']) : $data['new'];
            $mail->line("- **{$field}**: Changed from '{$old}' to '{$new}'");
        }

        return $mail->action('View Planner', url("/projects/{$this->project->id}/planner"))
                    ->line('Please review these changes immediately to ensure project baseline integrity.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'plan_overwritten',
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'task_id' => $this->task ? $this->task->id : null,
            'task_title' => $this->task ? $this->task->title : null,
            'overwritten_by' => $this->overwriter->name,
            'changes' => $this->changes,
            'message' => "Locked plan for " . ($this->task ? $this->task->title : $this->project->name) . " was modified by {$this->overwriter->name}."
        ];
    }
}
