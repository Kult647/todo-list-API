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
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'shared_tasks' => TaskResource::collection($this->whenLoaded('sharedTasks')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
