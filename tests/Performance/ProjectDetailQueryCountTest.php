<?php

namespace Tests\Performance;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProjectDetailQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_detail_page_query_count_remains_constant_with_many_tasks(): void
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Scale Project',
        ]);

        // Buat 25 tugas dan 15 activity logs di dalam proyek
        for ($i = 1; $i <= 25; $i++) {
            Task::create([
                'project_id' => $project->id,
                'title' => "Tugas Skala #{$i}",
                'status' => 'pending',
            ]);
        }

        for ($j = 1; $j <= 15; $j++) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'PROJECT_UPDATED',
                'subject_type' => Project::class,
                'subject_id' => $project->id,
                'payload' => ['iteration' => $j],
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($user)->get(route('projects.show', $project));

        $response->assertOk();

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Halaman detail proyek hanya boleh mengeksekusi jumlah query tetap (proyek, eager-loaded tasks, eager-loaded activity logs, session/user)
        // Maksimal <= 10 query untuk 25 tugas dan 15 log
        $this->assertLessThanOrEqual(
            10,
            $queryCount,
            "Terdeteksi pemborosan query pada halaman detail proyek! Jumlah query yang dieksekusi: {$queryCount}."
        );
    }
}
