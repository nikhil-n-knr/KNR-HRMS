<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskChecklist extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'content', 'is_completed', 'position', 'assigned_to'];

    protected $casts = [
        'is_completed' => 'boolean',
        'position' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
