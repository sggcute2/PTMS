<?php

namespace Tests\Unit\Actions;

use App\Actions\CreateTaskAction;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_task_successfully(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Project for CreateTaskAction',
        ]);

        $action = app(CreateTaskAction::class);

        $task = $action->execute($user, [
            'project_id' => $project->id,
            'title' => 'Task via Action',
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Task via Action', $task->title);
        $this->assertEquals($project->id, $task->project_id);
        $this->assertDatabaseHas('tasks', ['title' => 'Task via Action']);
    }
}
