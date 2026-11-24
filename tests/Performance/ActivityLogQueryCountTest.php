<?php

namespace Tests\Performance;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLogQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_listing_prevents_n_plus_one_via_eager_loading(): void
    {
        $user = User::factory()->create();

        // Buat beberapa log dengan relasi user dan subject polimorfik
        for ($i = 1; $i <= 20; $i++) {
            $project = Project::create([
                'user_id' => $user->id,
                'name' => "Log Project {$i}",
            ]);

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'PROJECT_CREATED',
                'subject_type' => Project::class,
                'subject_id' => $project->id,
                'payload' => ['index' => $i],
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($user)->get(route('activity_logs.index'));

        $response->assertOk();

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Harus menggunakan eager loading ('user', 'subject') + count distinct action + pagination count
        // Maksimal <= 8 query untuk 20 data polymorphic
        $this->assertLessThanOrEqual(
            8,
            $queryCount,
            "Terdeteksi N+1 query pada halaman riwayat aktivitas! Jumlah query yang dieksekusi: {$queryCount}."
        );
    }
}
