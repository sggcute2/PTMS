<?php

namespace Tests\Security;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SqlInjectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_search_is_safe_against_sql_injection(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Security Project']);

        Task::create([
            'project_id' => $project->id,
            'title' => 'Normal Task',
            'status' => 'pending',
        ]);

        // Payload SQL Injection klasik
        $payloads = [
            "' OR '1'='1",
            "admin' --",
            "1'; DROP TABLE tasks; --",
            "UNION SELECT null, null, null--",
        ];

        foreach ($payloads as $payload) {
            $response = $this->actingAs($user)->get(route('tasks.index', ['search' => $payload]));
            $response->assertOk();
            // Memastikan query tidak crash dan tabel tidak rusak
            $this->assertDatabaseHas('tasks', ['title' => 'Normal Task']);
        }
    }

    public function test_activity_log_filter_is_safe_against_sql_injection(): void
    {
        $user = User::factory()->create();

        $payload = "' OR 1=1 --";
        $response = $this->actingAs($user)->get(route('activity_logs.index', ['action' => $payload]));

        $response->assertOk();
    }
}
