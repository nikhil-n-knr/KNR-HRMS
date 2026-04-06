<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardLayoutController extends Controller
{
    /**
     * Save the user's dashboard layout preferences (X, Y, W, H for grid stack)
     */
    public function saveLayout(Request $request)
    {
        $user = auth()->user();
        $layout = $request->input('layout');

        if (!is_array($layout)) {
            return response()->json(['error' => 'Invalid layout data'], 400);
        }

        $preferences = $user->preferences ?? [];
        $preferences['dashboard_layout'] = $layout;

        $user->preferences = $preferences;
        $user->save();

        return response()->json([
            'message' => 'Layout saved successfully',
            'preferences' => $user->preferences
        ]);
    }
}
