<?php

namespace Tests\Unit\Services;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ActivityLogService $activityLogService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->activityLogService = app(ActivityLogService::class);
    }

    public function test_can_record_manual_activity_log(): void
    {
        $user = User::factory()->create();

        $log = $this->activityLogService->log(
            $user,
            'CUSTOM_AUDIT_ACTION',
            Project::class,
            123,
            ['keterangan' => 'Audit manual berhasil dilakukan']
        );

        $this->assertInstanceOf(ActivityLog::class, $log);
        $this->assertEquals('CUSTOM_AUDIT_ACTION', $log->action);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('Audit manual berhasil dilakukan', $log->payload['keterangan']);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'CUSTOM_AUDIT_ACTION',
            'user_id' => $user->id,
        ]);
    }

    public function test_get_user_logs_filters_by_action_and_dates(): void
    {
        $user = User::factory()->create();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'LOGIN_SUCCESS',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'PASSWORD_CHANGED',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'created_at' => Carbon::now()->subDay(),
        ]);

        $filteredByAction = $this->activityLogService->getUserLogs($user, ['action' => 'LOGIN_SUCCESS']);
        $this->assertCount(1, $filteredByAction);
        $this->assertEquals('LOGIN_SUCCESS', $filteredByAction->first()->action);

        $filteredByDate = $this->activityLogService->getUserLogs($user, [
            'start_date' => Carbon::now()->subDays(2)->toDateString(),
            'end_date' => Carbon::now()->toDateString(),
        ]);
        $this->assertCount(1, $filteredByDate);
        $this->assertEquals('PASSWORD_CHANGED', $filteredByDate->first()->action);
    }

    public function test_get_available_actions_returns_distinct_list(): void
    {
        $user = User::factory()->create();

        ActivityLog::create(['user_id' => $user->id, 'action' => 'ACTION_A', 'subject_type' => User::class, 'subject_id' => $user->id]);
        ActivityLog::create(['user_id' => $user->id, 'action' => 'ACTION_A', 'subject_type' => User::class, 'subject_id' => $user->id]);
        ActivityLog::create(['user_id' => $user->id, 'action' => 'ACTION_B', 'subject_type' => User::class, 'subject_id' => $user->id]);

        $actions = $this->activityLogService->getAvailableActions($user);

        $this->assertCount(2, $actions);
        $this->assertTrue($actions->contains('ACTION_A'));
        $this->assertTrue($actions->contains('ACTION_B'));
    }
}
