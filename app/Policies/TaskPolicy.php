<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Task $task)
    {
        // Разрешаем просмотр если:
        // 1. Пользователь - автор задачи
        // 2. Пользователь - соисполнитель
        // 3. Пользователь - участник проекта, к которому относится задача

        return $task->author_id === $user->id
            || $task->assignees->contains($user->id)
            || ($task->project && $task->project->users->contains($user->id));
    }

    public function update(User $user, Task $task)
    {
        return $task->author_id === $user->id;
    }

    public function delete(User $user, Task $task)
    {
        return $task->author_id === $user->id;
    }

    public function restore(User $user, Task $task)
    {
        return $task->author_id === $user->id;
    }
}
