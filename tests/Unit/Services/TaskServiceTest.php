<?php

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaskService $taskService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskService = app(TaskService::class);
    }

    public function test_can_create_task(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Test Project']);

        $task = $this->taskService->createTask($user, [
            'project_id' => $project->id,
            'title' => 'Implementasi Unit Test',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Implementasi Unit Test', $task->title);
        $this->assertDatabaseHas('tasks', ['title' => 'Implementasi Unit Test']);
    }

    public function test_cannot_create_task_under_other_users_project(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $project2 = Project::create(['user_id' => $user2->id, 'name' => 'Project User 2']);

        $this->expectException(ValidationException::class);

        $this->taskService->createTask($user1, [
            'project_id' => $project2->id,
            'title' => 'Tugas Ilegal',
        ]);
    }

    public function test_can_update_task(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Test Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Task Lama', 'status' => 'pending']);

        $updated = $this->taskService->updateTask($user, $task, [
            'project_id' => $project->id,
            'title' => 'Task Baru',
            'status' => 'pending',
        ]);

        $this->assertEquals('Task Baru', $updated->title);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Task Baru']);
    }

    public function test_advance_status_transitions_valid_sequence(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Task 1', 'status' => 'pending']);

        // pending -> in_progress
        $this->taskService->advanceStatus($task);
        $this->assertEquals('in_progress', $task->fresh()->status);

        // in_progress -> completed
        $this->taskService->advanceStatus($task->fresh());
        $this->assertEquals('completed', $task->fresh()->status);

        // completed cannot advance further
        $this->expectException(ValidationException::class);
        $this->taskService->advanceStatus($task->fresh());
    }

    public function test_advance_status_rejects_skipping_stages(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Task Skip', 'status' => 'pending']);

        $this->expectException(ValidationException::class);
        $this->taskService->advanceStatus($task, 'completed');
    }

    public function test_can_delete_task(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Task to delete', 'status' => 'pending']);

        $result = $this->taskService->deleteTask($task);

        $this->assertTrue($result);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
