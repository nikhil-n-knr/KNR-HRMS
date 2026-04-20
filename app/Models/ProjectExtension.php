<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectExtension extends Model
{
    use HasFactory;

    /**
     * Category constants for the 3 extension scenarios.
     *
     * PRIORITY_CONFLICT : Person was pulled to other work, actual invested hours were less.
     *                     Same scope but timeline pushed. Days extend, effort gap captured.
     * SCOPE_CHANGE      : Wrong estimation / new features / alterations requested.
     *                     Both hours AND days increase, possibly more resources needed.
     * COMPLEXITY_DRAG   : Execution slower than planned due to technical complexity.
     *                     Both hours and days consumed proportionally more than planned.
     */
    const CATEGORY_PRIORITY_CONFLICT = 'priority_conflict';
    const CATEGORY_SCOPE_CHANGE      = 'scope_change';
    const CATEGORY_COMPLEXITY_DRAG   = 'complexity_drag';

    const CATEGORIES = [
        self::CATEGORY_PRIORITY_CONFLICT => 'Priority Conflict',
        self::CATEGORY_SCOPE_CHANGE      => 'Scope Change / Wrong Estimation',
        self::CATEGORY_COMPLEXITY_DRAG   => 'Complexity Drag',
    ];

    protected $fillable = [
        'project_id',
        'task_id',
        'type',
        'category',
        'hours_added',
        'days_added',
        'reason',
        'notes',
        'original_start_date',
        'original_end_date',
        'extended_end_date',
        'extension_meta',
        'created_by',
    ];

    protected $casts = [
        'original_start_date' => 'date:Y-m-d',
        'original_end_date'   => 'date:Y-m-d',
        'extended_end_date'   => 'date:Y-m-d',
        'hours_added'         => 'float',
        'days_added'          => 'integer',
        'extension_meta'      => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Human-readable category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category ?? ''));
    }
}
