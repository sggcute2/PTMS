<?php

namespace Tests\Feature\Projects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_project_detail_and_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Proyek Saya',
        ]);

        $project->tasks()->create([
            'title' => 'Tugas dalam proyek',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('projects.show', $project));

        $response->assertOk();
        $response->assertSee('Proyek Saya');
        $response->assertSee('Tugas dalam proyek');
    }
}
