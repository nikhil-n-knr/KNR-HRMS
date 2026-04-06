<?php

namespace App\Http\Middleware\Project;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Project;

class ScopeCreepGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to Task Creation/Modification methods
        if (!$request->isMethod('post') && !$request->isMethod('put')) {
            return $next($request);
        }

        // Identify Project
        // Case 1: Route param 'project'
        $project = $request->route('project');
        
        // Case 2: Payload 'project_id' (if direct task create)
        if (!$project && $request->has('project_id')) {
            $project = Project::find($request->project_id);
        }

        if ($project && $project->status === 'active') {
            // Check for Manager Override
            if (!$request->hasHeader('X-Manager-Override')) {
                // If header missing, we could throw 403 OR we return a special JSON 
                // that tells frontend "Confirmation Required".
                
                // For simplified "Strict Mode", we throw 403.
                // In a real UI, this would trigger a modal "Sprint is Active. Force Add?" -> sends request again with header.
                return response()->json([
                    'message' => 'Scope Creep Detected: This project is in an Active Sprint. Manager Override required.',
                    'code' => 'SCOPE_CREEP',
                    'required_header' => 'X-Manager-Override'
                ], 403);
            }
        }

        return $next($request);
    }
}
