<?php

namespace Tests\Integration;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_cannot_be_created_with_non_existent_project_id(): void
    {
        $this->expectException(QueryException::class);

        Task::create([
            'project_id' => 999999, // ID proyek yang tidak ada
            'title' => 'Orphan Task',
            'status' => 'pending',
        ]);
    }

    public function test_user_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $this->expectException(QueryException::class);

        User::factory()->create(['email' => 'duplicate@example.com']);
    }

    public function test_project_requires_valid_user_foreign_key(): void
    {
        $this->expectException(QueryException::class);

        Project::create([
            'user_id' => 999999, // ID user yang tidak ada
            'name' => 'Invalid Project',
        ]);
    }
}
