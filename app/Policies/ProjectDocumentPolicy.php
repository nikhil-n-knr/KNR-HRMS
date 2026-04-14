<?php

namespace App\Policies;

use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectDocument $projectDocument): bool
    {
        // 1. Super Admin / Admin / Manager can view everything
        if ($user->hasRole(['Super Admin', 'Admin', 'Manager'])) {
            return true;
        }

        // 2. If document is management_only, restricted to above roles
        if ($projectDocument->visibility === 'management_only') {
            return false;
        }

        // 3. Client Users can only view documents explicitly shared with clients
        if ($user->client_id) {
            return $projectDocument->visibility === 'client_shared' 
                && $projectDocument->project->client_id === $user->client_id;
        }

        // 4. Default Internal (Employees)
        return $projectDocument->visibility === 'internal' || $projectDocument->visibility === 'public';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Controlled by Controller logic (Project access)
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectDocument $projectDocument): bool
    {
        return $user->id === $projectDocument->uploader_id || $user->hasRole(['Admin', 'Manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectDocument $projectDocument): bool
    {
        // Clients can never delete documents (for audit trail)
        if ($user->client_id) {
            return false;
        }

        return $user->id === $projectDocument->uploader_id || $user->hasRole(['Admin', 'Manager']);
    }
}
