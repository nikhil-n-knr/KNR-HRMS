<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientManagementController extends Controller
{
    public function index()
    {
        $clients = Client::with(['clientUsers.projects', 'projects'])->get();
        // Since we don't have a direct relation from Client to Project in the standardized schema yet 
        // (usually it's Project belongsTo Client), let's fetch projects separately or rely on the relation if it exists.
        // Assuming Project belongsTo Client.
        
        return \Inertia\Inertia::render('Project/Clients/Index', [
            'clients' => $clients,
            'all_projects' => Project::select('id', 'name', 'client_id')->get()
        ]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string',
            'email' => 'required|email|unique:client_users,email',
            'project_ids' => 'array'
        ]);

        $password = Str::random(10); // Generate initial random password

        $user = ClientUser::create([
            'client_id' => $request->client_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        if ($request->project_ids) {
            $user->projects()->sync($request->project_ids);
        }

        // Ideally send email with $password here
        
        session()->flash('flash_password', $password);
        
        return back()->with('success', 'User created successfully.')
            ->setStatusCode(303);
    }

    public function updateUser(Request $request, ClientUser $user)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:client_users,email,' . $user->id,
            'project_ids' => 'array',
            'is_active' => 'boolean'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->is_active
        ]);

        if ($request->has('project_ids')) {
            $user->projects()->sync($request->project_ids);
        }

        return back()->with('success', 'User updated successfully.')
            ->setStatusCode(303);
    }

    public function resetPassword(Request $request, ClientUser $user)
    {
        $request->validate([
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        $password = $request->password ?? Str::random(12);
        
        $user->update(['password' => Hash::make($password)]);
        
        if (!$request->password) {
            session()->flash('flash_password', $password);
        }
        
        return back()->with('success', 'Password updated successfully.')
            ->setStatusCode(303);
    }

    public function killSwitch(ClientUser $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'Activated' : 'Suspended';
        return back()->with('success', "User access {$status} successfully.")
            ->setStatusCode(303);
    }
}
