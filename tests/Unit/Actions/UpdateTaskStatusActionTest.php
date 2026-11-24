<?php

namespace Tests\Unit\Actions;

use App\Actions\UpdateTaskStatusAction;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskStatusActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_task_status_successfully(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Project for UpdateTaskStatusAction',
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Status Advance Task',
            'status' => 'pending',
        ]);

        $action = app(UpdateTaskStatusAction::class);

        $updated = $action->execute($task, 'in_progress');

        $this->assertInstanceOf(Task::class, $updated);
        $this->assertEquals('in_progress', $updated->status);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);
    }
}
