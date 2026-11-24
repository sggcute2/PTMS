<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskService
{
    /**
     * Peta transisi status yang valid (hanya boleh maju satu tahap)
     */
    public const ALLOWED_TRANSITIONS = [
        'pending' => 'in_progress',
        'in_progress' => 'completed',
    ];

    /**
     * Mendapatkan tugas-tugas milik user dengan pencarian judul, filter status, filter overdue, dan eager loading
     */
    public function getUserTasks(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Task::whereHas('project', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['project' => function ($q) {
                $q->withTrashed();
            }]);

        // 1. Pencarian berdasarkan judul tugas
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('title', 'like', "%{$search}%");
        }

        // 2. Filter berdasarkan status tugas (pending, in_progress, completed)
        if (!empty($filters['status']) && in_array($filters['status'], ['pending', 'in_progress', 'completed'])) {
            $query->where('status', $filters['status']);
        }

        // 3. Filter berdasarkan proyek
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        // 4. Filter khusus untuk tugas yang melewati due_date dan belum selesai
        if (!empty($filters['overdue']) && $filters['overdue'] === '1') {
            $query->where('status', '!=', 'completed')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', Carbon::today());
        }

        return $query->latest('id')->paginate(10)->withQueryString();
    }

    /**
     * Membuat tugas baru, memastikan proyek valid dan milik user yang sama
     */
    public function createTask(User $user, array $data): Task
    {
        $project = Project::where('id', $data['project_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$project) {
            throw ValidationException::withMessages([
                'project_id' => 'Proyek tidak ditemukan atau bukan milik Anda.',
            ]);
        }

        return DB::transaction(function () use ($project, $data) {
            return Task::create([
                'project_id' => $project->id,
                'title' => $data['title'],
                'status' => $data['status'] ?? 'pending',
                'due_date' => $data['due_date'] ?? null,
            ]);
        });
    }

    /**
     * Memperbarui data tugas
     */
    public function updateTask(User $user, Task $task, array $data): Task
    {
        if ($task->project_id != $data['project_id']) {
            $project = Project::where('id', $data['project_id'])
                ->where('user_id', $user->id)
                ->first();

            if (!$project) {
                throw ValidationException::withMessages([
                    'project_id' => 'Proyek tujuan tidak ditemukan atau bukan milik Anda.',
                ]);
            }
        }

        $task->update([
            'project_id' => $data['project_id'],
            'title' => $data['title'],
            'status' => $data['status'],
            'due_date' => $data['due_date'] ?? null,
        ]);

        return $task;
    }

    /**
     * Memajukan status tugas satu tahap secara aman (concurrency-safe dengan pessimistic lock lockForUpdate)
     */
    public function advanceStatus(Task $task, ?string $expectedNextStatus = null): Task
    {
        return DB::transaction(function () use ($task, $expectedNextStatus) {
            /** @var Task $lockedTask */
            $lockedTask = Task::where('id', $task->id)->lockForUpdate()->firstOrFail();

            $currentStatus = $lockedTask->status;

            if (!isset(self::ALLOWED_TRANSITIONS[$currentStatus])) {
                throw ValidationException::withMessages([
                    'status' => 'Tugas sudah berada pada tahap akhir (' . ucfirst(str_replace('_', ' ', $currentStatus)) . ') dan tidak dapat dimajukan lagi.',
                ]);
            }

            $nextStatus = self::ALLOWED_TRANSITIONS[$currentStatus];

            if ($expectedNextStatus !== null && $expectedNextStatus !== $nextStatus) {
                throw ValidationException::withMessages([
                    'status' => "Perubahan status tidak valid. Status saat ini adalah '{$currentStatus}', hanya dapat diubah ke '{$nextStatus}'.",
                ]);
            }

            $lockedTask->status = $nextStatus;
            $lockedTask->save();

            return $lockedTask;
        });
    }

    /**
     * Quick status update umum (dengan validasi tahapan berurutan)
     */
    public function updateStatus(Task $task, string $newStatus): Task
    {
        return $this->advanceStatus($task, $newStatus);
    }

    /**
     * Soft delete tugas
     */
    public function deleteTask(Task $task): bool
    {
        return $task->delete();
    }
}
