<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskPullRequest;
use Illuminate\Http\Request;

class TaskPullRequestController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
        ]);

        $pr = $task->pullRequests()->create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'url' => $validated['url'],
            'status' => 'pending'
        ]);

        // Log Activity
        $task->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'pr_linked',
            'details' => ['title' => $pr->title, 'url' => $pr->url]
        ]);

        return response()->json($pr->load('user'));
    }

    public function update(Request $request, TaskPullRequest $pr)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,merged,rejected',
            'remarks' => 'nullable|string'
        ]);

        $oldStatus = $pr->status;
        $pr->update($validated);

        if ($oldStatus !== $pr->status) {
             $pr->task->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'pr_status_updated',
                'details' => [
                    'pr_title' => $pr->title, 
                    'old_status' => $oldStatus, 
                    'new_status' => $pr->status
                ]
            ]);
        }

        return response()->json($pr->load('user'));
    }

    public function destroy(TaskPullRequest $pr)
    {
        $pr->delete();
        return response()->json(['success' => true]);
    }
}
