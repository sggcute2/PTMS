<?php

namespace Tests\Feature\ActivityLogs;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_activity_logs_page(): void
    {
        $user = User::factory()->create();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'PROJECT_CREATED',
            'subject_type' => Project::class,
            'subject_id' => 1,
            'payload' => ['name' => 'Proyek Audit'],
        ]);

        $response = $this->actingAs($user)->get(route('activity_logs.index'));

        $response->assertOk();
        $response->assertSee('PROJECT_CREATED');
    }
}
