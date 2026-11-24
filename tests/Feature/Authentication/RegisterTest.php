<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_registration_page(): void
    {
        $response = $this->get(route('register'));
        $response->assertOk();
        $response->assertSee('Daftar Akun Baru');
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_registration_requires_valid_email_and_password_confirmation(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'secret',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }
}
