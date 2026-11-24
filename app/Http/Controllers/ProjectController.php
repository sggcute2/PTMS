<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {
    }

    public function index(Request $request)
    {
        $projects = $this->projectService->getUserProjects(
            $request->user(),
            $request->only(['search'])
        );

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request)
    {
        $project = $this->projectService->createProject(
            $request->user(),
            $request->validated()
        );

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proyek berhasil dibuat.');
    }

    public function show(Project $project)
    {
        // Enforce Policy: pengguna hanya boleh melihat proyek miliknya
        Gate::authorize('view', $project);

        // Eager load tasks untuk menghindari N+1 query
        $project->load([
            'tasks' => function ($query) {
                $query->latest();
            }
        ]);

        // Muat audit trail untuk proyek ini
        $activityLogs = $project->activityLogs()->with('user')->take(20)->get();

        return view('projects.show', compact('project', 'activityLogs'));
    }

    public function edit(Project $project)
    {
        // Enforce Policy
        Gate::authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        // Enforce Policy
        Gate::authorize('update', $project);

        $this->projectService->updateProject($project, $request->validated());

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        // Enforce Policy
        Gate::authorize('delete', $project);

        $this->projectService->deleteProject($project);

        return redirect()->route('projects.index')
            ->with('success', 'Proyek dan seluruh tugas di dalamnya berhasil dihapus.');
    }
}
