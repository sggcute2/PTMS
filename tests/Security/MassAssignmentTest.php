<?php

namespace Tests\Security;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_override_project_owner_via_mass_assignment(): void
    {
        $user = User::factory()->create();
        $victim = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Test Project',
            'user_id' => $victim->id, // Mencoba merekayasa kepemilikan
        ]);

        $response->assertRedirect();

        $project = Project::where('name', 'Test Project')->first();
        $this->assertNotNull($project);
        $this->assertEquals($user->id, $project->user_id, 'user_id harus tetap milik pengguna yang login');
        $this->assertNotEquals($victim->id, $project->user_id);
    }

    public function test_cannot_mass_assign_unfillable_attributes_on_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Project']);

        $task = new Task();
        $task->fill([
            'project_id' => $project->id,
            'title' => 'Task Title',
            'status' => 'pending',
            'id' => 9999, // Unfillable / guarded
            'is_admin_flag' => true,
        ]);
        $task->save();

        $this->assertNotEquals(9999, $task->id);
    }
}
