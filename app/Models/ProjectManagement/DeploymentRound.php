<?php

namespace App\Models\ProjectManagement;

use App\Models\BugTicket;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeploymentRound extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'version', 'status', 'notes', 'deployed_at'];

    protected $casts = [
        'deployed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tickets()
    {
        return $this->hasMany(BugTicket::class);
    }
}
