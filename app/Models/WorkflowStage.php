<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStage extends Model
{
    protected $fillable = [
        'workflow_id',
        'name',
        'stage_order',
        'approver_type',
        'role_id',
        'user_id',
        'can_reject',
        'can_edit',
        'auto_approve_after_hours',
        'is_parallel',
        'team_id',
        'department_id',
        'additional_approvers',
        'approval_strategy',
        'approval_strategy',
        'allow_self_approval',
        'is_client_visible',
        'reminder_hours',
        'is_final',
        'notify_incharge',
        'mentor_id',
        'color',
        'transition_rules',
    ];

    protected $casts = [
        'stage_order' => 'integer',
        'can_reject' => 'boolean',
        'can_edit' => 'boolean',
        'auto_approve_after_hours' => 'integer',
        'is_parallel' => 'boolean',
        'additional_approvers' => 'array',
        'transition_rules' => 'array',
    ];

    /**
     * Get the workflow this stage belongs to
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the role (if approver_type is 'role')
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the specific user (if approver_type is 'specific_user')
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the specific mentor for this stage
     */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    /**
     * Get the specific team (if approver_type is 'team')
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the specific department (if approver_type is 'department')
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Resolve who should approve based on approver_type
     */
    public function resolveApprover(User $employee): ?User
    {
        return $this->resolveApproverByType($this->approver_type, $this->role_id, $this->user_id, $this->team_id, $this->department_id, $employee);
    }

    /**
     * Resolve generic approver by type and ID
     */
    private function resolveApproverByType(string $type, ?int $roleId, ?int $userId, ?int $teamId, ?int $deptId, User $employee): ?User
    {
        $hrProfile = $employee->employee;

        return match($type) {
            'manager' => $hrProfile?->manager,
            'team_lead' => $employee->team?->manager,
            'role' => User::whereHas('roles', fn($q) => $q->where('roles.id', $roleId))->first(),
            'specific_user' => User::find($userId),
            'department_head' => $employee->department?->head,
            'team' => Team::find($teamId)?->manager,
            'department' => Department::find($deptId)?->head,
            default => null,
        };
    }

    /**
     * Resolve all approvers for this stage including additional ones
     * @return \Illuminate\Support\Collection
     */
    public function resolveAllApprovers(User $employee)
    {
        $approvers = collect();

        // 1. Primary Approver
        if ($primary = $this->resolveApprover($employee)) {
            $approvers->push($primary);
        }

        // 2. Additional Approvers
        if (!empty($this->additional_approvers) && is_array($this->additional_approvers)) {
            foreach ($this->additional_approvers as $config) {
                if (empty($config['type'])) continue;

                $roleId = $config['role_id'] ?? null;
                $userId = $config['user_id'] ?? null;
                // For simplified JSON config, maybe we just store 'id' and 'type'
                // But let's support robust config
                $teamId = $config['team_id'] ?? null;
                $deptId = $config['department_id'] ?? null;

                if ($extra = $this->resolveApproverByType($config['type'], $roleId, $userId, $teamId, $deptId, $employee)) {
                    $approvers->push($extra);
                }
            }
        }

        return $approvers->unique('id');
    }
}
