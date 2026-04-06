<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\OfferLetter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function index()
    {
        // 1. Total Applications (All Time)
        $totalApplications = JobApplication::count();

        // 2. Total Hired
        $totalHired = JobApplication::where('status', 'Hired')->count();

        // 3. Average Time to Hire (in Days)
        // Calculated as diff between `created_at` and `updated_at` where status is Hired
        // This is a rough approximation. Ideally we'd have a 'hired_at' timestamp.
        $hiredApps = JobApplication::where('status', 'Hired')->get();
        $avgTimeToHire = $hiredApps->avg(function ($app) {
            return $app->created_at->diffInDays($app->updated_at);
        });

        // 4. Offer Acceptance Rate
        $totalOffers = OfferLetter::count();
        $acceptedOffers = OfferLetter::where('status', 'Accepted')->count();
        $acceptanceRate = $totalOffers > 0 ? round(($acceptedOffers / $totalOffers) * 100, 1) : 0;

        // 5. Applications Last 30 Days (Chart Data)
        $applicationsTrend = JobApplication::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 6. Pipeline Breakdown (Funnel)
        $pipeline = JobApplication::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return Inertia::render('Talent/Reports/Index', [
            'metrics' => [
                'total_applications' => $totalApplications,
                'total_hired' => $totalHired,
                'avg_time_to_hire' => round($avgTimeToHire ?? 0, 1),
                'acceptance_rate' => $acceptanceRate
            ],
            'charts' => [
                'trend' => $applicationsTrend,
                'pipeline' => $pipeline
            ]
        ]);
    }
}
