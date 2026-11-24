<?php

namespace Tests\Feature\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_tasks_by_status(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Proyek']);

        $taskPending = Task::create(['project_id' => $project->id, 'title' => 'Tugas Belum Mulai', 'status' => 'pending']);
        $taskProgress = Task::create(['project_id' => $project->id, 'title' => 'Tugas Sedang Jalan', 'status' => 'in_progress']);

        $response = $this->actingAs($user)->get(route('tasks.index', ['status' => 'in_progress']));

        $response->assertOk();
        $response->assertSee('Tugas Sedang Jalan');
        $response->assertDontSee('Tugas Belum Mulai');
    }
}
