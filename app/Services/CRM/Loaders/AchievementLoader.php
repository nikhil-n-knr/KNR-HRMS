<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\UserStat;
use App\Models\CRM\UserAchievement;

class AchievementLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'overview' => $this->loadLeaderboard($request),
            'badges' => $this->loadBadges($request),
            default => $this->loadLeaderboard($request),
        };
    }

    private function loadLeaderboard(Request $request)
    {
        $tenantId = $this->tenantId;
        return [
            'leaderboard' => UserStat::where('tenant_id', $tenantId)->with('user')->orderByDesc('total_points')->take(10)->get(),
            'monthly_best' => UserStat::where('tenant_id', $tenantId)->orderByDesc('total_points')->first()?->user_id,
            'top_daily' =>'₹4.2L / day'
        ];
    }

    private function loadBadges(Request $request)
    {
        return [
            'badges' => UserAchievement::where('user_id', auth()->id())->get()
        ];
    }
}
