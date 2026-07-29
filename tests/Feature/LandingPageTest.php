<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_contains_the_required_sections_and_authentication_links(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('TaskFlow')
            ->assertSee('Make room for work')
            ->assertSee('Everything in one flow')
            ->assertSee('href="'.url('/app/register').'"', escape: false)
            ->assertSee('href="'.url('/app/login').'"', escape: false);
    }

    public function test_landing_page_authentication_destinations_are_available(): void
    {
        $this->get('/app/login')->assertOk();
        $this->get('/app/register')->assertOk();
    }
}
