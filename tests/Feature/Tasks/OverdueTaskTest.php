<?php

namespace Tests\Feature\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverdueTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_overdue_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Proyek']);

        $overdueTask = Task::create([
            'project_id' => $project->id,
            'title' => 'Tugas Telat Deadline',
            'status' => 'pending',
            'due_date' => Carbon::yesterday(),
        ]);

        $futureTask = Task::create([
            'project_id' => $project->id,
            'title' => 'Tugas Masih Aman',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow(),
        ]);

        $this->assertTrue($overdueTask->is_overdue);
        $this->assertFalse($futureTask->is_overdue);

        $response = $this->actingAs($user)->get(route('tasks.index', ['overdue' => '1']));

        $response->assertOk();
        $response->assertSee('Tugas Telat Deadline');
        $response->assertDontSee('Tugas Masih Aman');
    }
}
