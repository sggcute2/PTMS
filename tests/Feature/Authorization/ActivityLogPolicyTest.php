<?php

namespace Tests\Feature\Authorization;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_other_users_activity_log(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $log = ActivityLog::create([
            'user_id' => $user2->id,
            'action' => 'PROJECT_CREATED',
            'subject_type' => Project::class,
            'subject_id' => 99,
            'payload' => ['name' => 'Data Rahasia'],
        ]);

        $response = $this->actingAs($user1)->get(route('activity_logs.show', $log));
        $response->assertStatus(403);
        $response->assertSee('Akses ke Riwayat Audit Dibatasi');
    }

    public function test_user_can_view_own_activity_log(): void
    {
        $user = User::factory()->create();

        $log = ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'PROJECT_CREATED',
            'subject_type' => Project::class,
            'subject_id' => 10,
            'payload' => ['name' => 'Data Sendiri'],
        ]);

        $response = $this->actingAs($user)->get(route('activity_logs.show', $log));
        $response->assertStatus(200);
        $response->assertSee('Detail Riwayat Aktivitas');
    }
}
