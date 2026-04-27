<?php

namespace App\Http\Requests\ProjectManagement;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Add 'create_projects' permission check if needed.
        // For now, assume any authorized user reaching this route (via Gate) is allowed.
        return true; 
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:projects,code',
            'visibility' => 'required|in:public,team_locked,stealth,freelancer_mode',
            'status' => 'required|in:planning,active',
            'dates.start' => 'nullable|date',
            'dates.end' => 'nullable|date|after_or_equal:dates.start',
            'modules' => 'nullable|array',
            'modules.*.name' => 'required_with:modules|string',
            'owners' => 'nullable|array',
            'owners.*' => 'exists:users,id'
        ];
    }

    public function messages()
    {
        return [
            'client_id.required' => 'Please select a client.',
            'modules.*.name.required_with' => 'All modules must have a name.',
        ];
    }
}
