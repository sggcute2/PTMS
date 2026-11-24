<?php

namespace Tests\Unit\Actions;

use App\Actions\UpdateProjectAction;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_project_successfully(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Old Action Project Name',
            'description' => 'Old description',
        ]);

        $action = app(UpdateProjectAction::class);
        $updated = $action->execute($project, [
            'name' => 'New Action Project Name',
            'description' => 'Updated description',
        ]);

        $this->assertInstanceOf(Project::class, $updated);
        $this->assertEquals('New Action Project Name', $updated->name);
        $this->assertEquals('Updated description', $updated->description);
        $this->assertDatabaseHas('projects', ['name' => 'New Action Project Name']);
    }
}
