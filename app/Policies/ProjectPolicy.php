<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Project $project)
    {
        return $project->users->contains($user->id);
    }

    public function update(User $user, Project $project)
    {
        return $project->users->contains($user->id);
    }

    public function delete(User $user, Project $project)
    {
        return $project->users->contains($user->id);
    }
}
