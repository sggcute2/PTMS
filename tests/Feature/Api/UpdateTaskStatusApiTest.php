<?php

namespace Tests\Feature\Api;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskStatusApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_can_advance_task_status_and_records_activity_log(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'API Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'API Task', 'status' => 'pending']);

        $response = $this->actingAs($user)->patchJson(route('api.tasks.updateStatus', $task), [
            'status' => 'in_progress',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'id' => $task->id,
                'status' => 'in_progress',
            ]
        ]);

        $this->assertEquals('in_progress', $task->fresh()->status);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'TASK_STATUS_UPDATED',
            'subject_id' => $task->id,
        ]);
    }

    public function test_api_denies_unauthorized_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $project2 = Project::create(['user_id' => $user2->id, 'name' => 'Project 2']);
        $task2 = Task::create(['project_id' => $project2->id, 'title' => 'Task 2', 'status' => 'pending']);

        $response = $this->actingAs($user1)->patchJson(route('api.tasks.updateStatus', $task2), [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(403);
    }

    public function test_api_throttling_rate_limit(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Throttled Project']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Task', 'status' => 'pending']);

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->patchJson(route('api.tasks.updateStatus', $task), [
                'status' => 'in_progress',
            ]);
        }

        $rateLimited = $this->actingAs($user)->patchJson(route('api.tasks.updateStatus', $task), [
            'status' => 'in_progress',
        ]);

        $rateLimited->assertStatus(429);
    }
}
