<?php

namespace App\Traits;

use App\Services\Infrastructure\ActivityLogger;

trait LogsActivity
{
    /**
     * Boot the trait.
     */
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            app(ActivityLogger::class)->log(
                "Created " . class_basename($model),
                $model,
                $model->toArray()
            );
        });

        static::updated(function ($model) {
             // Only log changed attributes
            $changes = $model->getChanges();
            app(ActivityLogger::class)->log(
                "Updated " . class_basename($model),
                $model,
                ['changes' => $changes]
            );
        });

        static::deleted(function ($model) {
            app(ActivityLogger::class)->log(
                "Deleted " . class_basename($model),
                $model
            );
        });
    }
}
