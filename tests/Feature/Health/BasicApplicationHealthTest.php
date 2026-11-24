<?php

namespace Tests\Feature;

use Tests\TestCase;

class BasicApplicationHealthTest extends TestCase
{

    public function test_basic_application_health(): void
    {
        $response = $this->get('/up');
        $response->assertOk();
    }
}
