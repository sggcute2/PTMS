<?php

namespace Tests\Feature\Projects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_project_with_tasks_atomically(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Proyek Baru dari Web',
            'description' => 'Deskripsi proyek',
            'tasks' => [
                ['title' => 'Tugas 1', 'status' => 'pending'],
                ['title' => 'Tugas 2', 'status' => 'in_progress'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['name' => 'Proyek Baru dari Web', 'user_id' => $user->id]);
        $this->assertDatabaseHas('tasks', ['title' => 'Tugas 1']);
        $this->assertDatabaseHas('tasks', ['title' => 'Tugas 2']);
    }

    public function test_create_project_validation_fails_when_name_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }
}
