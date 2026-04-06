<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_system' => $this->is_system,
            'users_count' => $this->whenCounted('users'),
            'permissions' => $this->whenLoaded('permissions', function() {
                return $this->permissions->map(function($p) {
                    return [
                        'id' => $p->id,
                        'module' => $p->module,
                        'submodule' => $p->submodule,
                        'action' => $p->action,
                    ];
                });
            }),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
