<?php

namespace Tests\Feature\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_soft_delete_own_task(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Proyek']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Tugas Hapus', 'status' => 'pending']);

        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task));

        $response->assertRedirect();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
