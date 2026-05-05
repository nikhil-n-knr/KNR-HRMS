<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    protected $logger;
    protected $activityService;

    public function __construct(
        \App\Services\Infrastructure\LoggerService $logger,
        \App\Services\ProjectManagement\TaskActivityService $activityService
    )
    {
        $this->logger = $logger;
        $this->activityService = $activityService;
    }

    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'files.*' => 'nullable|file|max:10240' // 10MB limit
        ]);

        $attachments = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Store in public disk
                $path = $file->store('attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => '/storage/' . $path,
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize()
                ];
            }
        }

        $comment = $task->comments()->create([
            'body' => $validated['body'],
            'user_id' => auth()->id(),
            'attachments' => !empty($attachments) ? $attachments : null
        ]);

        // Log Activity (User Facing)
        $this->activityService->log($task, 'comment', [
            'comment_id' => $comment->id,
            'body_snippet' => \Illuminate\Support\Str::limit($comment->body, 50)
        ]);
        
        // Log Audit (System)
        $this->logger->log('Task', 'Comment', "User commented on task '{$task->title}'", [
            'task_id' => $task->id, 
            'comment_id' => $comment->id,
            'attachments_count' => count($attachments)
        ]);

        return response()->json($comment->load('author'));
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }
        
        $comment->delete();
        
        $this->logger->log('Task', 'Comment Delete', "User deleted comment on task", [
            'comment_id' => $comment->id
        ]);

        return response()->json(['message' => 'Deleted']);
    }
}
