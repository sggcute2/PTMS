<?php

namespace Tests\Unit\Actions;

use App\Actions\CreateProjectAction;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_project_successfully(): void
    {
        $user = User::factory()->create();
        $action = app(CreateProjectAction::class);

        $project = $action->execute($user, [
            'name' => 'Project from Action',
            'description' => 'Created via CreateProjectAction',
            'tasks' => [
                ['title' => 'Action Task', 'status' => 'pending']
            ]
        ]);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals('Project from Action', $project->name);
        $this->assertEquals($user->id, $project->user_id);
        $this->assertDatabaseHas('projects', ['name' => 'Project from Action']);
        $this->assertDatabaseHas('tasks', ['title' => 'Action Task']);
    }
}
