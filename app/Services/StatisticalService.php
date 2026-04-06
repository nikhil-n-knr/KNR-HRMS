<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatisticalService
{
    /**
     * Calculate the Mean (Average) of a dataset.
     *
     * @param array|Collection $data Array of numbers
     * @return float
     */
    public function calculateMean($data): float
    {
        if (empty($data) || (is_object($data) && $data->isEmpty())) {
            return 0.0;
        }

        $collection = $data instanceof Collection ? $data : collect($data);
        return round($collection->average(), 2);
    }

    /**
     * Calculate the Median of a dataset.
     *
     * @param array|Collection $data Array of numbers
     * @return float
     */
    public function calculateMedian($data): float
    {
        if (empty($data) || (is_object($data) && $data->isEmpty())) {
            return 0.0;
        }

        $collection = $data instanceof Collection ? $data : collect($data);
        $sorted = $collection->sort()->values();
        $count = $sorted->count();
        $middle = floor($count / 2);

        if ($count % 2) {
            return $sorted[$middle];
        }

        return round(($sorted[$middle - 1] + $sorted[$middle]) / 2, 2);
    }

    /**
     * Calculate Standard Deviation (Consistency).
     * Lower value = Higher Consistency.
     *
     * @param array|Collection $data
     * @return float
     */
    public function calculateStandardDeviation($data): float
    {
        if (empty($data) || (is_object($data) && $data->isEmpty())) {
            return 0.0;
        }

        $collection = $data instanceof Collection ? $data : collect($data);
        $mean = $collection->average();
        $count = $collection->count();

        if ($count === 0) return 0.0;

        // Variance
        $variance = $collection->reduce(function ($carry, $item) use ($mean) {
            return $carry + pow($item - $mean, 2);
        }, 0) / $count;

        return round(sqrt($variance), 2);
    }

    /**
     * Detect Outliers using IQR method.
     * Return array of indices or values that are outliers.
     *
     * @param array|Collection $data
     * @return array ['low' => [], 'high' => []]
     */
    public function detectOutliers($data): array
    {
        $collection = $data instanceof Collection ? $data : collect($data);
        if ($collection->count() < 4) return ['low' => [], 'high' => []];

        $sorted = $collection->sort()->values();
        $q1 = $sorted[floor($sorted->count() / 4)];
        $q3 = $sorted[floor($sorted->count() * 3 / 4)];
        $iqr = $q3 - $q1;

        $lowerBound = $q1 - (1.5 * $iqr);
        $upperBound = $q3 + (1.5 * $iqr);

        return [
            'low' => $collection->filter(fn($v) => $v < $lowerBound)->values()->all(),
            'high' => $collection->filter(fn($v) => $v > $upperBound)->values()->all()
        ];
    }

    /**
     * Get Top and Bottom performers from an associative array (User => Value).
     * 
     * @param array $data Associative array ['John' => 50, 'Doe' => 20]
     * @param int $limit
     * @return array ['top' => [], 'bottom' => []]
     */
    public function getRankings(array $data, int $limit = 5): array
    {
        arsort($data);
        $top = array_slice($data, 0, $limit, true);
        
        asort($data);
        $bottom = array_slice($data, 0, $limit, true);
        
        return ['top' => $top, 'bottom' => $bottom];
    }

    /**
     * Count items above and below a threshold (e.g. Mean).
     *
     * @param array|Collection $data
     * @param float $threshold
     * @return array ['above' => int, 'below' => int, 'equal' => int]
     */
    public function countAboveBelow($data, float $threshold): array
    {
        $collection = $data instanceof Collection ? $data : collect($data);
        
        return [
            'above' => $collection->filter(fn($v) => $v > $threshold)->count(),
            'below' => $collection->filter(fn($v) => $v < $threshold)->count(),
            'equal' => $collection->filter(fn($v) => $v == $threshold)->count(),
        ];
    }

    /**
     * Calculate Attendance Score (0-100) based on factors.
     * 
     * @param int $durationMinutes
     * @param bool $isLate
     * @return int
     */
    public function calculateDailyScore(int $durationMinutes, bool $isLate): int
    {
        $score = 100;
        
        // Late Penalty
        if ($isLate) $score -= 10;
        
        // Duration Logic (Assume 9 hours = 540 mins)
        if ($durationMinutes < 540) {
            // Deduct 1 point for every 10 mins short
            $shortfall = 540 - $durationMinutes;
            $score -= floor($shortfall / 10);
        }

        return max(0, $score);
    }
}
