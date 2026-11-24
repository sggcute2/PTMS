<?php

namespace Tests\Performance;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_does_not_suffer_from_n_plus_one_query_problem(): void
    {
        $user = User::factory()->create();

        // Buat 10 proyek dan sejumlah tugas di masing-masing proyek
        for ($p = 1; $p <= 10; $p++) {
            $project = Project::create([
                'user_id' => $user->id,
                'name' => "Performance Project {$p}",
            ]);

            for ($t = 1; $t <= 5; $t++) {
                Task::create([
                    'project_id' => $project->id,
                    'title' => "Task {$p}-{$t}",
                    'status' => $t % 2 === 0 ? 'in_progress' : 'pending',
                ]);
            }
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Dashboard agregasi harus efisien (menggunakan subquery/conditional aggregates, bukan looping query per proyek)
        // Ambang batas maksimum yang sangat aman: <= 12 query untuk 10 proyek
        $this->assertLessThanOrEqual(
            12,
            $queryCount,
            "Terdeteksi potensi query N+1 pada Dashboard! Jumlah query yang dieksekusi: {$queryCount}."
        );
    }
}
