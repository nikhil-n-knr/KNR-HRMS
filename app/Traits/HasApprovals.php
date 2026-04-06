<?php

namespace App\Traits;

use App\Models\Approval;
use App\Services\ApprovalService;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasApprovals
{
    /**
     * Get the approval record associated with the model.
     */
    public function approval(): MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable');
    }

    /**
     * Check if the model is currently in an approval process.
     */
    public function isUnderReview(): bool
    {
        return $this->approval()->where('status', 'pending')->exists();
    }

    /**
     * Check if the model has been approved.
     */
    public function isApproved(): bool
    {
        return $this->approval()->where('status', 'approved')->exists();
    }

    /**
     * Submit this model for approval.
     */
    public function submitForApproval($requester, string $event)
    {
        $service = app(ApprovalService::class);
        return $service->initiate($this, $requester, $event);
    }
}
