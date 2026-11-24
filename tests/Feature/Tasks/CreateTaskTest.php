<?php

namespace Tests\Feature\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_task_under_own_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Proyek Test Task',
        ]);

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'project_id' => $project->id,
            'title' => 'Tugas Baru dari Web',
            'status' => 'pending',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Tugas Baru dari Web',
        ]);
    }
}
