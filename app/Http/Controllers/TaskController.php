<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function index(Request $request)
    {
        $tasks = $this->taskService->getUserTasks(
            $request->user(),
            $request->only(['search', 'status', 'project_id', 'overdue'])
        );

        $projects = $request->user()->projects()->select('id', 'name')->get();

        // Hitung jumlah tugas yang telah melewati batas waktu (overdue) untuk badge info
        $overdueCount = Task::whereHas('project', fn($q) => $q->where('user_id', $request->user()->id))
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', Carbon::today())
            ->count();

        return view('tasks.index', compact('tasks', 'projects', 'overdueCount'));
    }

    public function create(Request $request)
    {
        $projects = $request->user()->projects()->select('id', 'name')->get();
        $selectedProjectId = $request->query('project_id');

        return view('tasks.create', compact('projects', 'selectedProjectId'));
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->createTask(
            $request->user(),
            $request->validated()
        );

        return redirect()->route('projects.show', $task->project_id)
            ->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function show(Task $task)
    {
        Gate::authorize('view', $task);

        $task->load(['project' => function ($q) {
            $q->withTrashed();
        }]);

        $activityLogs = $task->activityLogs()->with('user')->take(20)->get();

        return view('tasks.show', compact('task', 'activityLogs'));
    }

    public function edit(Task $task)
    {
        Gate::authorize('update', $task);

        $projects = request()->user()->projects()->select('id', 'name')->get();

        return view('tasks.edit', compact('task', 'projects'));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);

        $this->taskService->updateTask($request->user(), $task, $request->validated());

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function advanceStatus(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $expectedStatus = $request->input('target_status');

        try {
            $updatedTask = $this->taskService->advanceStatus($task, $expectedStatus);

            return back()->with(
                'success',
                "Status tugas '{$updatedTask->title}' berhasil dimajukan menjadi " . ucfirst(str_replace('_', ' ', $updatedTask->status)) . '.'
            );
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage() ?: 'Perubahan status ditolak.');
        }
    }

    public function updateStatus(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        try {
            $this->taskService->updateStatus($task, $validated['status']);

            return back()->with('success', 'Status tugas berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage() ?: 'Perubahan status ditolak.');
        }
    }

    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);

        $projectId = $task->project_id;
        $this->taskService->deleteTask($task);

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Tugas berhasil dihapus (soft delete) dan dicatat pada audit log.');
    }
}
