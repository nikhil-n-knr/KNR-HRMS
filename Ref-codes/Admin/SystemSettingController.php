<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SystemSetting;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all();
        
        // Transform to key-value map for easier frontend consumption
        $mappedSettings = [];
        foreach ($settings as $setting) {
             $mappedSettings[$setting->key] = $setting->value;
        }

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $mappedSettings
        ]);
    }

    public function store(Request $request)
    {
        // Expects an array of settings: { key: value } or simple fields
        $data = $request->all();

        foreach ($data as $key => $value) {
            // Check if file
            if ($request->hasFile($key)) {
                $path = $request->file($key)->store('settings', 'public');
                $value = Storage::url($path);
            }
            // Skip large payloads or non-settings if any (optional filter)
            
            SystemSetting::set($key, $value);
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
