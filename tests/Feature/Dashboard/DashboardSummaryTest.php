<?php

namespace Tests\Feature\Dashboard;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_aggregated_project_task_summaries(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Project Dashboard']);

        Task::create(['project_id' => $project->id, 'title' => 'Tugas 1', 'status' => 'pending']);
        Task::create(['project_id' => $project->id, 'title' => 'Tugas 2', 'status' => 'in_progress']);
        Task::create(['project_id' => $project->id, 'title' => 'Tugas 3', 'status' => 'completed']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Project Dashboard');
        $response->assertViewHas('projectTaskSummaries', function ($summaries) use ($project) {
            $item = $summaries->firstWhere('id', $project->id);
            return $item
                && $item->total_tasks_count === 3
                && $item->pending_tasks_count === 1
                && $item->in_progress_tasks_count === 1
                && $item->completed_tasks_count === 1;
        });
    }
}
