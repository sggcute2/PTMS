<?php

namespace Tests\Security;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_request_without_csrf_token_is_rejected(): void
    {
        $middleware = new class(app(), app('encrypter')) extends \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken {
            protected function runningUnitTests()
            {
                return false; // Paksa pengecekan CSRF dijalankan pada unit test
            }
        };

        $session = session()->driver();
        $session->start();
        $session->put('_token', 'valid-server-token');

        $request = \Illuminate\Http\Request::create(route('projects.store'), 'POST', [
            'name' => 'CSRF Vulnerable Project',
            '_token' => 'attacker-fake-token',
        ]);
        $request->setLaravelSession($session);

        $this->expectException(\Illuminate\Session\TokenMismatchException::class);

        $middleware->handle($request, function () {
            return response('OK');
        });
    }
}
