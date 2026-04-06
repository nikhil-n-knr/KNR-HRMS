<?php

namespace App\Services\CMS\Loaders;

use Illuminate\Http\Request;
use App\Models\CMS\Theme;

class ThemeLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'active' => $this->loadActiveTheme($request),
            'library' => $this->loadLibrary($request),
            default => $this->loadActiveTheme($request),
        };
    }

    private function loadActiveTheme(Request $request)
    {
        $theme = Theme::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->first();

        return [
            'active_theme' => $theme
        ];
    }
    
    private function loadLibrary(Request $request)
    {
        return [
            'themes' => Theme::where('tenant_id', $this->tenantId)->get()
        ];
    }
}
