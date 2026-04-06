<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectIncident extends Model
{
    protected $fillable = [
        'project_id',
        'declared_by',
        'title',
        'description',
        'type',
        'severity',
        'status',
        'lifecycle_stage',
        'data_impact_map',
        'security_details',
        'resolved_at'
    ];

    protected $casts = [
        'data_impact_map' => 'array',
        'security_details' => 'array',
        'resolved_at' => 'datetime'
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function commander()
    {
        return $this->belongsTo(User::class, 'declared_by');
    }
}
