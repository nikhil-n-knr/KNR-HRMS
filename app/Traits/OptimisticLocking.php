<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

trait OptimisticLocking
{
    /**
     * Boot the trait.
     * Ensure that when saving, we check versions.
     */
    public static function bootOptimisticLocking()
    {
        static::updating(function (Model $model) {
            // Check if version was passed in input (usually via request)
            // We use 'request()' helper as a quick proxy, but ideal is explicit checking.
            // For now, let's assume the Controller handles the 'check' logic, 
            // OR we automate it here if the dirty fields include business logic.
            
            // AUTOMATED CHECK:
            // If the model is dirty, we increment version.
            $model->version = $model->version + 1;
        });
    }

    /**
     * Check if the provided version matches the database version.
     * Call this from Controller before update().
     */
    public function checkVersion($inputVersion)
    {
        if ($inputVersion && $this->version != $inputVersion) {
            throw ValidationException::withMessages([
                'version' => 'The record has been modified by another user. Please reload and try again.'
            ]);
        }
    }
}
