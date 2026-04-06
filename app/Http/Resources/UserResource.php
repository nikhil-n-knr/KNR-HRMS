<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'employee_id' => $this->employee_id,
            'status' => $this->status,
            'employee_type' => $this->employee_type,
            'department' => $this->department ? $this->department->name : null,
            'location' => $this->location ? $this->location->name : null,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
