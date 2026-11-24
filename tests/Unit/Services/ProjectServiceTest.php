<?php

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProjectService $projectService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectService = app(ProjectService::class);
    }

    public function test_can_create_project_with_initial_tasks(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Proyek Alpha',
            'description' => 'Deskripsi proyek baru',
            'tasks' => [
                ['title' => 'Tugas Pertama', 'status' => 'pending'],
                ['title' => 'Tugas Kedua', 'status' => 'in_progress'],
            ],
        ];

        $project = $this->projectService->createProject($user, $data);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals('Proyek Alpha', $project->name);
        $this->assertEquals($user->id, $project->user_id);
        $this->assertCount(2, $project->tasks);
        $this->assertDatabaseHas('projects', ['name' => 'Proyek Alpha']);
        $this->assertDatabaseHas('tasks', ['title' => 'Tugas Pertama', 'status' => 'pending']);
        $this->assertDatabaseHas('tasks', ['title' => 'Tugas Kedua', 'status' => 'in_progress']);
    }

    public function test_can_update_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Nama Lama',
            'description' => 'Deskripsi Lama',
        ]);

        $updated = $this->projectService->updateProject($project, [
            'name' => 'Nama Baru',
            'description' => 'Deskripsi Baru',
        ]);

        $this->assertEquals('Nama Baru', $updated->name);
        $this->assertEquals('Deskripsi Baru', $updated->description);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Nama Baru']);
    }

    public function test_can_soft_delete_project_and_its_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Proyek Dihapus',
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Tugas Proyek',
            'status' => 'pending',
        ]);

        $result = $this->projectService->deleteProject($project);

        $this->assertTrue($result);
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_can_restore_project_and_its_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['user_id' => $user->id, 'name' => 'Proyek']);
        $task = Task::create(['project_id' => $project->id, 'title' => 'Tugas', 'status' => 'pending']);

        $this->projectService->deleteProject($project);
        $this->assertTrue($project->trashed());

        $this->projectService->restoreProject($project);

        $this->assertFalse($project->fresh()->trashed());
        $this->assertFalse($task->fresh()->trashed());
    }

    public function test_get_user_projects_paginates_and_filters_correctly(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Project::create(['user_id' => $user->id, 'name' => 'Aplikasi Web Laravel']);
        Project::create(['user_id' => $user->id, 'name' => 'Mobile App Flutter']);
        Project::create(['user_id' => $otherUser->id, 'name' => 'Aplikasi Milik Orang Lain']);

        $projects = $this->projectService->getUserProjects($user, ['search' => 'Web']);

        $this->assertCount(1, $projects);
        $this->assertEquals('Aplikasi Web Laravel', $projects->first()->name);
    }
}
