<?php

namespace Tests\Feature\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_search_tasks_by_title(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Proyek']);

        $task1 = Task::create(['project_id' => $project->id, 'title' => 'Implementasi Autentikasi', 'status' => 'pending']);
        $task2 = Task::create(['project_id' => $project->id, 'title' => 'Desain Mockup Figma', 'status' => 'pending']);

        $response = $this->actingAs($user)->get(route('tasks.index', ['search' => 'Autentikasi']));

        $response->assertOk();
        $response->assertSee('Implementasi Autentikasi');
        $response->assertDontSee('Desain Mockup Figma');
    }
}
