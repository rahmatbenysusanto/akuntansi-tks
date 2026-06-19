<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_home_redirects(): void
    {
        $response = $this->get('/');
        $response->assertRedirect();
    }
}
