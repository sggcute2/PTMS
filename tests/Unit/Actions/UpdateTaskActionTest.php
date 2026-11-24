<?php

namespace Tests\Unit\Actions;

use App\Actions\UpdateTaskAction;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_task_successfully(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Project for UpdateTaskAction',
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Task Title Before',
            'status' => 'pending',
        ]);

        $action = app(UpdateTaskAction::class);

        $updated = $action->execute($user, $task, [
            'project_id' => $project->id,
            'title' => 'Task Title After',
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Task::class, $updated);
        $this->assertEquals('Task Title After', $updated->title);
        $this->assertDatabaseHas('tasks', ['title' => 'Task Title After']);
    }
}
