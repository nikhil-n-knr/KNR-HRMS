<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\IdentityCard;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IdentityAssetController extends Controller
{
    /**
     * Bulk Upload Profile Images
     * Matches filename (e.g. EMP001.jpg) to employee_code
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $results = [
            'matched' => 0,
            'errors' => []
        ];

        foreach ($request->file('images') as $file) {
            $filename = $file->getClientOriginalName();
            // Expected format: EMP001.jpg or EMP001_some_text.jpg
            $code = strtoupper(explode('.', $filename)[0]);
            // If there's an underscore, take first part
            $code = explode('_', $code)[0];

            $employee = Employee::where('employee_code', $code)->first();

            if ($employee) {
                // Store image with timestamp to avoid cache issues
                $extension = $file->getClientOriginalExtension();
                $newFilename = "{$code}_" . time() . "." . $extension;
                $path = $file->storeAs('avatars', $newFilename, 'public');
                
                // Delete old avatar if exists
                if ($employee->avatar) {
                    Storage::disk('public')->delete($employee->avatar);
                }
                
                $employee->update(['avatar' => $path]);
                
                // If there's a card in 'Pending Photo', move it to 'Draft' or 'In Production'
                IdentityCard::where('user_id', $employee->user_id)
                    ->where('status', 'Pending Photo')
                    ->update(['status' => 'Draft']);

                $results['matched']++;
            } else {
                $results['errors'][] = "No match for file: {$filename} (Extracted Code: {$code})";
            }
        }

        return back()->with('success', "Processed {$results['matched']} images. " . count($results['errors']) . " errors.")
                     ->with('upload_results', $results);
    }

    /**
     * Individual Profile Image Upload
     */
    public function individualUpload(Request $request, $employeeId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $employee = Employee::findOrFail($employeeId);
        $file = $request->file('image');
        $filename = "EMP{$employee->employee_code}_" . time() . "." . $file->getClientOriginalExtension();
        
        $path = $file->storeAs('avatars', $filename, 'public');
        
        if ($employee->avatar) {
            Storage::disk('public')->delete($employee->avatar);
        }
        
        $employee->update(['avatar' => $path]);

        // Trigger lifecycle change
        IdentityCard::where('user_id', $employee->user_id)
            ->where('status', 'Pending Photo')
            ->update(['status' => 'Draft']);

        return back()->with('success', "Profile image updated for {$employee->first_name}");
    }
}
