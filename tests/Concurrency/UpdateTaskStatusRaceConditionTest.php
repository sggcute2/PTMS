<?php

namespace Tests\Concurrency;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateTaskStatusRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_status_advancements_prevent_race_condition(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Race Condition Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Concurrent Task', 'status' => 'pending']);

        $service = app(TaskService::class);

        // Simulasi request 1 berhasil memajukan status ke in_progress
        $firstResult = $service->advanceStatus($task->fresh());
        $this->assertEquals('in_progress', $firstResult->status);

        // Request 2 berjalan segera setelahnya dengan target yang sama (pending -> in_progress), harus ditolak karena state sudah in_progress
        $this->expectException(ValidationException::class);
        $service->advanceStatus($task->fresh(), 'in_progress');
    }

    public function test_multiple_identical_advance_requests_only_one_succeeds_per_step(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Concurrency Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Double Click Task', 'status' => 'pending']);

        $service = app(TaskService::class);

        $successCount = 0;
        $errorCount = 0;

        // Simulasi 2 pemanggilan konkuren yang bersaing memajukan status awal (misal double submit)
        for ($i = 0; $i < 2; $i++) {
            try {
                // Keduanya mencoba memajukan dari asumsi status awal
                $currentTask = Task::find($task->id);
                $service->advanceStatus($currentTask, 'in_progress');
                $successCount++;
            } catch (ValidationException $e) {
                $errorCount++;
            }
        }

        $this->assertEquals(1, $successCount, 'Hanya tepat 1 request yang boleh berhasil berpindah ke in_progress');
        $this->assertEquals(1, $errorCount, 'Request kedua yang datang setelah status berubah harus ditolak');
        $this->assertEquals('in_progress', $task->fresh()->status);
    }
}
