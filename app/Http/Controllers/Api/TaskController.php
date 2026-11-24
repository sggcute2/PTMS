<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * API JSON Endpoint untuk mengubah status tugas (hanya boleh maju satu tahap)
     * Dilengkapi rate limiting dan otorisasi Policy
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        // Enforce Policy: Menolak akses ke tugas milik pengguna lain
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed'],
        ], [
            'status.required' => 'Field status wajib diisi.',
            'status.in' => 'Status tidak valid. Nilai yang diperbolehkan: pending, in_progress, completed.',
        ]);

        try {
            // advanceStatus melakukan validasi transisi satu tahap + pessimistic locking + DB::transaction
            $updatedTask = $this->taskService->advanceStatus($task, $validated['status']);

            return response()->json([
                'status' => 'success',
                'message' => 'Status tugas berhasil diperbarui.',
                'data' => [
                    'id' => $updatedTask->id,
                    'project_id' => $updatedTask->project_id,
                    'title' => $updatedTask->title,
                    'status' => $updatedTask->status,
                    'due_date' => $updatedTask->due_date?->format('Y-m-d'),
                    'is_overdue' => $updatedTask->is_overdue,
                    'updated_at' => $updatedTask->updated_at?->toIso8601String(),
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
