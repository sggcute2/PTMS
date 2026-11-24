<?php

namespace Tests\Security;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XssProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_xss_script_payload_is_properly_escaped_on_render(): void
    {
        $user = User::factory()->create();
        $xssPayload = '<script>alert("XSS")</script>';

        $project = Project::create([
            'user_id' => $user->id,
            'name' => $xssPayload,
            'description' => '<img src=x onerror=alert(1)>',
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => '<svg/onload=alert("task")>',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('projects.show', $project));

        $response->assertOk();

        // Blade secara otomatis meng-escape data dengan htmlspecialchars ({{ ... }})
        // Script tag mentah tidak boleh dieksekusi / harus ter-escape
        $response->assertDontSee('<script>alert("XSS")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', false);
    }
}
