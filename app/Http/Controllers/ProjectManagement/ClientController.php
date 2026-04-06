<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query()->withCount('projects');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Project/ClientList', [
            'clients' => $clients,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:clients,code',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date|after_or_equal:contract_start',
            'portal_access' => 'boolean'
        ]);

        Client::create($validated);

        return redirect()->back()->with('success', 'Client created successfully.')->setStatusCode(303);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:clients,code,' . $client->id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date|after_or_equal:contract_start',
            'portal_access' => 'boolean'
        ]);

        $client->update($validated);

        return redirect()->back()->with('success', 'Client updated successfully.')->setStatusCode(303);
    }

    public function show(Client $client)
    {
        $client->load(['projects' => function($q) {
            $q->select('id', 'client_id', 'code', 'name', 'status', 'deadline');
        }, 'clientUsers']);
        
        return Inertia::render('Project/Client/Show', [
            'client' => $client
        ]);
    }

    public function destroy(Client $client)
    {
        if ($client->projects()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete client with active projects.')->setStatusCode(303);
        }
        
        $client->delete();
        return redirect()->back()->with('success', 'Client deleted successfully.')->setStatusCode(303);
    }
    public function inviteUser(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:client_users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = \App\Models\ClientUser::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'client_id' => $client->id,
            'is_active' => true
        ]);

        // Automatically link to all current client projects for convenience
        $projectIds = $client->projects()->pluck('id')->toArray();
        $user->projects()->sync($projectIds);

        return redirect()->back()->with('success', 'Portal user created and linked to projects.')->setStatusCode(303);
    }
}
