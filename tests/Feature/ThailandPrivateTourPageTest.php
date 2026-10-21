<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThailandPrivateTourPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_thailand_private_tour_page_is_reachable_from_navigation(): void
    {
        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee(route('thailand'))
            ->assertSee('Bangkok')
            ->assertSee('Chiang Mai');

        $this->get(route('thailand'))
            ->assertOk()
            ->assertSee('Thailand Private Tours')
            ->assertSee('A land of colour.')
            ->assertSee('Where will Thailand take you?')
            ->assertSee('Bangkok')
            ->assertSee('Chiang Mai')
            ->assertSee('Pattaya')
            ->assertSee('Phuket')
            ->assertSee('Krabi')
            ->assertSee('Kanchanaburi')
            ->assertSee('role="region" aria-roledescription="carousel" aria-label="Thailand destinations"', false)
            ->assertSee('aria-label="Next destinations"', false)
            ->assertSee('Enquire on WhatsApp');
    }
}
