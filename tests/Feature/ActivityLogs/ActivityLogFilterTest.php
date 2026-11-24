<?php

namespace Tests\Feature\ActivityLogs;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_activity_logs_by_action_and_date(): void
    {
        $user = User::factory()->create();

        $log1 = ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'PROJECT_CREATED',
            'subject_type' => Project::class,
            'subject_id' => 1,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        $log2 = ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'TASK_CREATED',
            'subject_type' => Task::class,
            'subject_id' => 2,
            'created_at' => Carbon::now(),
        ]);

        $responseAction = $this->actingAs($user)->get(route('activity_logs.index', ['action' => 'PROJECT_CREATED']));
        $responseAction->assertOk();
        $responseAction->assertViewHas('logs', function ($logs) use ($log1, $log2) {
            return $logs->contains($log1) && !$logs->contains($log2);
        });
    }
}
