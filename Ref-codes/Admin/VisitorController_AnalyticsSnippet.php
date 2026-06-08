// Inside VisitorController index method
'analytics_chart' => VisitorPass::selectRaw('DATE(check_in_at) as date, count(*) as count')
    ->where('check_in_at', '>=', now()->subDays(7))
    ->groupBy('date')
    ->orderBy('date')
    ->get()
    ->map(function ($item) {
        return ['date' => $item->date, 'count' => $item->count];
    }),
