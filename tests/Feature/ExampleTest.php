<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // The home page queries categories and featured products, so it needs a
    // migrated database — the scaffold version of this test predates the store.
    use RefreshDatabase;

    public function test_the_home_page_returns_a_successful_response(): void
    {
        $this->get('/')->assertStatus(200);
    }
}
