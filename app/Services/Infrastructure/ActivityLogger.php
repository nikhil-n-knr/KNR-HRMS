<?php

namespace App\Services\Infrastructure;

use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log an activity.
     *
     * @param string $description
     * @param mixed|null $subject Model being acted on
     * @param array $properties Additional data
     * @param string $logName Category
     */
    public function log(string $description, $subject = null, array $properties = [], string $logName = 'default')
    {
        $user = Auth::user();

        $data = [
            'description' => $description,
            'log_name' => $logName,
            'properties' => array_merge([
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
            ], $properties),
            'ip_address' => request()->ip(),
            'causer_type' => $user ? get_class($user) : null,
            'causer_id' => $user ? $user->id : null,
        ];

        if ($subject) {
            $data['subject_type'] = get_class($subject);
            $data['subject_id'] = $subject->getKey();
        }

        LogActivityJob::dispatch($data);
    }
}
