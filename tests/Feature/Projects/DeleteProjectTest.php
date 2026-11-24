<?php

namespace Tests\Feature\Projects;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_soft_delete_own_project_and_its_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Proyek Siap Dihapus',
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Tugas Siap Dihapus',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->delete(route('projects.destroy', $project));

        $response->assertRedirect(route('projects.index'));
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
