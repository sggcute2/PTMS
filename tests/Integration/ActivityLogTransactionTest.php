<?php

namespace Tests\Integration;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ActivityLogService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLogTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_service_records_in_transaction_successfully(): void
    {
        $user = User::factory()->create();
        $service = app(ActivityLogService::class);

        $log = DB::transaction(function () use ($service, $user) {
            return $service->log(
                $user,
                'CRITICAL_TRANSACTION_EVENT',
                Project::class,
                555,
                ['status' => 'success']
            );
        });

        $this->assertDatabaseHas('activity_logs', [
            'id' => $log->id,
            'action' => 'CRITICAL_TRANSACTION_EVENT',
            'user_id' => $user->id,
        ]);
    }

    public function test_activity_log_is_rolled_back_when_enclosing_transaction_fails(): void
    {
        $user = User::factory()->create();
        $service = app(ActivityLogService::class);

        try {
            DB::transaction(function () use ($service, $user) {
                $service->log(
                    $user,
                    'FAILED_EVENT',
                    Project::class,
                    777,
                    ['status' => 'should_rollback']
                );

                throw new Exception('Transaction failure');
            });
        } catch (Exception $e) {
            // Expected exception
        }

        $this->assertDatabaseMissing('activity_logs', [
            'action' => 'FAILED_EVENT',
            'user_id' => $user->id,
        ]);
    }
}
