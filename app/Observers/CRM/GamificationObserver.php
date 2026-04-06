<?php

namespace App\Observers\CRM;

use App\Models\CRM\Deal;
use App\Models\CRM\Activity;
use App\Services\CRM\GamificationService;

class GamificationObserver
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated($model): void
    {
        if ($model instanceof Deal) {
            if ($model->wasChanged('status') && $model->status === 'won') {
                if ($model->assignee) {
                    $this->gamificationService->awardPoints(
                        $model->assignee, 
                        100, 
                        'deals_won', 
                        $model->value
                    );
                }
            }
        }

        if ($model instanceof Activity) {
            if ($model->wasChanged('is_completed') && $model->is_completed) {
                $user = $model->assignee ?: $model->creator;
                if ($user) {
                    $this->gamificationService->awardPoints(
                        $user, 
                        10, 
                        'activities'
                    );
                }
            }
        }
    }
    
    /**
     * Handle the Activity "created" event (for instant points)
     */
    public function activityCreated(Activity $activity): void
    {
        // Maybe award search points or log points
    }
}
