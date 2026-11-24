<?php

namespace Tests\Feature\Authorization;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_or_edit_other_users_project(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $project2 = Project::create([
            'user_id' => $user2->id,
            'name' => 'Proyek User 2',
        ]);

        $viewResponse = $this->actingAs($user1)->get(route('projects.show', $project2));
        $viewResponse->assertStatus(403);
        $viewResponse->assertSee('Akses ke Proyek Dibatasi');

        $editResponse = $this->actingAs($user1)->get(route('projects.edit', $project2));
        $editResponse->assertStatus(403);

        $updateResponse = $this->actingAs($user1)->put(route('projects.update', $project2), [
            'name' => 'Hacked Name',
        ]);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($user1)->delete(route('projects.destroy', $project2));
        $deleteResponse->assertStatus(403);
    }
}
