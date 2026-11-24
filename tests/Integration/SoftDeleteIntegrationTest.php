<?php

namespace Tests\Integration;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cascading_soft_delete_and_activity_logs_for_project_and_tasks(): void
    {
        $user = User::factory()->create();
        $service = app(ProjectService::class);

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Parent Project',
        ]);

        $task1 = Task::create(['project_id' => $project->id, 'title' => 'Task A', 'status' => 'pending']);
        $task2 = Task::create(['project_id' => $project->id, 'title' => 'Task B', 'status' => 'in_progress']);

        $service->deleteProject($project);

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task1->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task2->id]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'action' => 'PROJECT_DELETED',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Task::class,
            'subject_id' => $task1->id,
            'action' => 'TASK_DELETED',
        ]);
    }

    public function test_cascading_restore_restores_both_project_and_tasks(): void
    {
        $user = User::factory()->create();
        $service = app(ProjectService::class);

        $project = Project::create(['user_id' => $user->id, 'name' => 'Restore Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Task to restore', 'status' => 'pending']);

        $service->deleteProject($project);
        $this->assertTrue($project->trashed());
        $this->assertTrue($task->fresh()->trashed());

        $service->restoreProject($project);

        $this->assertFalse($project->fresh()->trashed());
        $this->assertFalse($task->fresh()->trashed());
    }
}
