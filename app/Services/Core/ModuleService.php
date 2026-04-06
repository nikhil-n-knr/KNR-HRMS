<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ModuleService
{
    /**
     * Get all active modules and their sub-modules, cached.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveModules()
    {
        return Cache::rememberForever('app_active_modules_tree', function () {
            // Fetch Modules
            $modules = DB::table('app_modules')
                ->where('status', true)
                ->orderBy('order')
                ->get();

            // Fetch Sub-modules
            $subModules = DB::table('app_sub_modules')
                ->where('status', true)
                ->orderBy('order')
                ->get()
                ->groupBy('module_id');

            // Attach sub-modules to modules
            return $modules->map(function ($module) use ($subModules) {
                $module->sub_modules = $subModules->get($module->id) ?? [];
                return $module;
            });
        });
    }

    /**
     * Clear module cache. call this after seed or update.
     */
    public function clearCache()
    {
        Cache::forget('app_active_modules_tree');
    }
}
