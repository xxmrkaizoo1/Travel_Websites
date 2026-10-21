<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhilippinesPrivateTourPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_philippines_private_tour_page_is_reachable_from_navigation(): void
    {
        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee(route('philippines'))
            ->assertSee('Manila')
            ->assertSee('Boracay');

        $this->get(route('philippines'))
            ->assertOk()
            ->assertSee('Philippines Private Tours')
            ->assertSee('A thousand island dreams.')
            ->assertSee('Where will the Philippines take you?')
            ->assertSee('Boracay')
            ->assertSee('Palawan')
            ->assertSee('Manila')
            ->assertSee('Cebu')
            ->assertSee('Bohol')
            ->assertSee('role="region" aria-roledescription="carousel" aria-label="Philippines destinations"', false)
            ->assertSee('aria-label="Next destinations"', false)
            ->assertSee('Enquire on WhatsApp');
    }
}
