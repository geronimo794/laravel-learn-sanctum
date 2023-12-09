<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_generate_token(): void
    {
        $response = $this->post('/tokens/create');

        $response->dump();

    }
}
