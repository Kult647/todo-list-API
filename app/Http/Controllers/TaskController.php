<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Task::query()
            ->where(function($query) use ($user) {
                $query->where('author_id', $user->id)
                    ->orWhereHas('assignees', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->orWhereHas('project.users', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
            })
            ->with(['author', 'project', 'assignees', 'tags'])
            ->latest();

        // Фильтрация по проекту
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Фильтрация по тегам
        if ($request->has('tag_ids')) {
            $tagIds = explode(',', $request->tag_ids);
            $query->whereHas('tags', function($query) use ($tagIds) {
                $query->whereIn('id', $tagIds);
            });
        }

        // Поиск по названию/описанию
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($query) use ($search) {
                $query->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        // Фильтрация по статусу выполнения
        if ($request->has('completed')) {
            $query->where('completed', $request->boolean('completed'));
        }

        $tasks = $query->paginate(10);

        return \App\Http\Resources\TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $request->user()->tasks()->create($request->validated());

        // Добавляем соисполнителей, если они указаны
        if ($request->has('assignee_ids')) {
            $task->assignees()->sync($request->assignee_ids);
        }

        // Добавляем теги, если они указаны
        if ($request->has('tag_ids')) {
            $task->tags()->sync($request->tag_ids);
        }

        return new \App\Http\Resources\TaskResource($task->load(['author', 'project', 'assignees', 'tags']));
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return new \App\Http\Resources\TaskResource($task->load(['author', 'project', 'assignees', 'tags']));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        // Обновляем соисполнителей, если они указаны
        if ($request->has('assignee_ids')) {
            $task->assignees()->sync($request->assignee_ids);
        }

        // Обновляем теги, если они указаны
        if ($request->has('tag_ids')) {
            $task->tags()->sync($request->tag_ids);
        }

        return new \App\Http\Resources\TaskResource($task->load(['author', 'project', 'assignees', 'tags']));
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return response()->noContent();
    }

    public function restore($taskId)
    {
        $task = Task::onlyTrashed()->findOrFail($taskId);
        $this->authorize('restore', $task);
        $task->restore();
        return new \App\Http\Resources\TaskResource($task->load(['author', 'project', 'assignees', 'tags']));
    }
}
