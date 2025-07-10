<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $projects = $request->user()->projects()
            ->with(['users', 'tasks'])
            ->latest()
            ->paginate(10);

        return \App\Http\Resources\ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());

        // Добавляем текущего пользователя как участника проекта
        $project->users()->attach($request->user()->id);

        return new \App\Http\Resources\ProjectResource($project->load(['users', 'tasks']));
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        return new \App\Http\Resources\ProjectResource($project->load(['users', 'tasks']));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);
        $project->update($request->validated());
        return new \App\Http\Resources\ProjectResource($project->load(['users', 'tasks']));
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->noContent();
    }

    public function addUser(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $project->users()->syncWithoutDetaching([$request->user_id]);

        return new \App\Http\Resources\ProjectResource($project->load(['users', 'tasks']));
    }

    public function removeUser(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $project->users()->detach($request->user_id);

        return new \App\Http\Resources\ProjectResource($project->load(['users', 'tasks']));
    }

}
