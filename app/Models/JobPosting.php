<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'notification_config' => 'array',
        'required_documents' => 'array',
        'valid_through' => 'date',
        'skills' => 'array',
        'stage_config' => 'array'
    ];

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function screeningTemplate()
    {
        return $this->belongsTo(ScreeningTemplate::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
