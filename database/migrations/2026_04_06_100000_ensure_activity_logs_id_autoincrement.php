<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure activity_logs.id has PRIMARY KEY and AUTO_INCREMENT.
     * This migration is idempotent and handles various database states.
     */
    public function up(): void
    {
        $desc = DB::select('DESCRIBE activity_logs');
        $idCol = collect($desc)->first(fn($c) => strtolower($c->Field ?? ($c->field ?? '')) === 'id');

        if (!$idCol) {
            // This case should not happen if the table exists, but let's be safe.
            return;
        }

        // Handle both object and array return types, and case sensitivity
        $colData = (object) $idCol;
        $field   = $colData->Field ?? ($colData->field ?? '');
        $key     = $colData->Key ?? ($colData->key ?? '');
        $extra   = $colData->Extra ?? ($colData->extra ?? '');

        $hasPK   = strtoupper($key) === 'PRI';
        $hasAuto = str_contains(strtolower($extra), 'auto_increment');

        if (!$hasAuto) {
            if (!$hasPK) {
                // No PK and no AutoInc - Add both
                DB::statement('ALTER TABLE activity_logs MODIFY id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT');
            } else {
                // Has PK but no AutoInc - Add AutoInc
                DB::statement('ALTER TABLE activity_logs MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            }
        }
    }

    public function down(): void
    {
        // Keep the fix in place.
    }
};
