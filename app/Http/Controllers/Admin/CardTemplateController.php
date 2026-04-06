<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\CardTemplate;
use Inertia\Inertia;

class CardTemplateController extends Controller
{
    /**
     * The Studio Canvas UI
     */
    public function studio($id = null)
    {
        $template = $id ? CardTemplate::findOrFail($id) : null;

        return Inertia::render('Admin/Identity/Studio/Index', [
            'template'          => $template,
            'existingTemplates' => CardTemplate::select('id', 'name', 'type', 'orientation', 'preview_image', 'design_data', 'is_default')->get()
        ]);
    }

    /**
     * Save / Update a template from the Studio
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id'            => 'nullable|exists:card_templates,id',
            'name'          => 'required|string|max:255',
            'type'          => 'required|string|in:Employee,Vendor,Visitor,Standard',
            'design_data'   => 'nullable|array',    // { front: {...}, back: {...}, version: '...' }
            'orientation'   => 'nullable|string|in:Portrait,Landscape',
            'dimensions'    => 'nullable|array',    // { width, height, preset, orientation }
            'preview_image' => 'nullable|string',   // base64 PNG — stored separately below
        ]);

        // ... (preview image logic stays the same) ...
        $previewPath = null;
        if (!empty($validated['preview_image'])) {
            $base64 = $validated['preview_image'];
            if (str_contains($base64, ',')) {
                $base64 = explode(',', $base64, 2)[1];
            }
            $filename = 'card_previews/' . uniqid('tpl_', true) . '.png';
            Storage::disk('public')->put($filename, base64_decode($base64));
            $previewPath = $filename;
        }

        // Determine dimensions from request or default
        $dimensions = $validated['dimensions'] ?? ['width' => 1011, 'height' => 638, 'dpi' => 300];

        // -----------------------------------------------------------------
        // Persist
        // -----------------------------------------------------------------
        $template = CardTemplate::updateOrCreate(
            ['id' => $request->id],
            [
                'name'          => $validated['name'],
                'type'          => $validated['type'],
                'orientation'   => $validated['orientation'] ?? 'Landscape',
                'design_data'   => $validated['design_data'] ?? null,
                'dimensions'    => $dimensions,
                'is_active'     => true,
                // Only overwrite preview if a new one was uploaded
                ...($previewPath ? ['preview_image' => $previewPath] : []),
            ]
        );

        return back()->with('success', 'Design "' . $template->name . '" saved successfully.');
    }

    public function toggleDefault($id)
    {
        $template = CardTemplate::findOrFail($id);
        
        // Remove default from others of same type
        CardTemplate::where('type', $template->type)->update(['is_default' => false]);
        
        $template->update(['is_default' => true]);
        
        return back()->with('success', $template->name . ' is now the default for ' . $template->type);
    }

    /**
     * Delete a template
     */
    public function destroy($id)
    {
        $template = CardTemplate::findOrFail($id);

        // Clean up preview image if stored
        if ($template->preview_image) {
            Storage::disk('public')->delete($template->preview_image);
        }

        $template->delete();

        return back()->with('success', 'Template deleted.');
    }
}