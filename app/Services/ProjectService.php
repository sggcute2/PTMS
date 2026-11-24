<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * Mendapatkan daftar proyek milik user dengan paginasi dan eager loading
     * Menghindari N+1 query dengan eager loading count task
     */
    public function getUserProjects(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Project::where('user_id', $user->id)
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($q) {
                    $q->where('status', 'completed');
                }
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    /**
     * Membuat proyek baru, opsional dengan tugas awal dalam 1 transaksi database
     */
    public function createProject(User $user, array $data): Project
    {
        return DB::transaction(function () use ($user, $data) {
            $project = Project::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            // Jika ada initial tasks yang diinputkan sekaligus
            if (!empty($data['tasks']) && is_array($data['tasks'])) {
                foreach ($data['tasks'] as $taskData) {
                    if (!empty($taskData['title'])) {
                        $project->tasks()->create([
                            'title' => $taskData['title'],
                            'status' => $taskData['status'] ?? 'pending',
                            'due_date' => $taskData['due_date'] ?? null,
                        ]);
                    }
                }
            }

            return $project;
        });
    }

    /**
     * Memperbarui proyek
     */
    public function updateProject(Project $project, array $data): Project
    {
        $project->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return $project;
    }

    /**
     * Soft delete proyek beserta seluruh tugas yang ada di dalamnya secara atomik
     */
    public function deleteProject(Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            // Soft delete seluruh tugas terkait terlebih dahulu (agar ter-trigger soft delete event & activity_logs)
            $project->tasks()->get()->each(function ($task) {
                $task->delete();
            });

            // Soft delete proyek itu sendiri (memicu event deleted & audit log)
            return $project->delete();
        });
    }

    /**
     * Memulihkan proyek beserta tugas-tugasnya
     */
    public function restoreProject(Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            $project->restore();
            // Restore tasks that were deleted together
            $project->tasks()->onlyTrashed()->get()->each(function ($task) {
                $task->restore();
            });

            return true;
        });
    }
}
