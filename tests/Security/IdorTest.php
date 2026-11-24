<?php

namespace Tests\Security;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_other_users_project_via_idor(): void
    {
        $attacker = User::factory()->create();
        $victim = User::factory()->create();

        $victimProject = Project::create([
            'user_id' => $victim->id,
            'name' => 'Victim Confidential Project',
        ]);

        $response = $this->actingAs($attacker)->get(route('projects.show', $victimProject));
        $response->assertStatus(403);
    }

    public function test_user_cannot_update_other_users_task_via_idor(): void
    {
        $attacker = User::factory()->create();
        $victim = User::factory()->create();

        $victimProject = Project::create([
            'user_id' => $victim->id,
            'name' => 'Victim Project',
        ]);

        $victimTask = Task::create([
            'project_id' => $victimProject->id,
            'title' => 'Victim Task',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($attacker)->put(route('tasks.update', $victimTask), [
            'project_id' => $victimProject->id,
            'title' => 'Hacked Task Title',
            'status' => 'pending',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('Victim Task', $victimTask->fresh()->title);
    }

    public function test_user_cannot_access_other_users_activity_log_via_idor(): void
    {
        $attacker = User::factory()->create();
        $victim = User::factory()->create();

        $log = ActivityLog::create([
            'user_id' => $victim->id,
            'action' => 'PROJECT_CREATED',
            'subject_type' => Project::class,
            'subject_id' => 10,
        ]);

        $response = $this->actingAs($attacker)->get(route('activity_logs.show', $log));
        $response->assertStatus(403);
    }
}
