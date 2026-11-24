<?php

namespace Tests\Feature\ActivityLogs;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_logs_are_paginated_properly(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 15; $i++) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => "ACTION_{$i}",
                'subject_type' => Project::class,
                'subject_id' => $i,
            ]);
        }

        $response = $this->actingAs($user)->get(route('activity_logs.index'));

        $response->assertOk();
        $response->assertViewHas('logs', function ($logs) {
            return $logs->count() === 10 && $logs->total() === 15;
        });
    }
}
