<?php

namespace Tests\Feature\Authorization;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_or_edit_other_users_task(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $project2 = Project::create([
            'user_id' => $user2->id,
            'name' => 'Proyek User 2',
        ]);

        $task2 = Task::create([
            'project_id' => $project2->id,
            'title' => 'Tugas User 2',
            'status' => 'pending',
        ]);

        $viewResponse = $this->actingAs($user1)->get(route('tasks.show', $task2));
        $viewResponse->assertStatus(403);
        $viewResponse->assertSee('Akses ke Tugas Dibatasi');

        $editResponse = $this->actingAs($user1)->get(route('tasks.edit', $task2));
        $editResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($user1)->delete(route('tasks.destroy', $task2));
        $deleteResponse->assertStatus(403);
    }
}
