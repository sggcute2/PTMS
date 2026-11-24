<?php

namespace Tests\Integration;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TaskStatusTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_status_update_and_audit_log_are_committed_together(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Integration Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Atomic Task', 'status' => 'pending']);

        $service = app(TaskService::class);
        $service->advanceStatus($task);

        $this->assertEquals('in_progress', $task->fresh()->status);
        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'TASK_STATUS_UPDATED',
        ]);
    }

    public function test_transaction_rolls_back_if_error_occurs_during_status_change(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Rollback Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Fail Task', 'status' => 'pending']);

        try {
            DB::transaction(function () use ($task) {
                $task->update(['status' => 'in_progress']);

                // Simulasi error tidak terduga dalam transaksi
                throw new Exception('Simulated database deadlock or server failure');
            });
        } catch (Exception $e) {
            // Expected exception
        }

        $this->assertEquals('pending', $task->fresh()->status);
        $this->assertDatabaseMissing('activity_logs', [
            'subject_id' => $task->id,
            'action' => 'TASK_STATUS_UPDATED',
        ]);
    }
}
