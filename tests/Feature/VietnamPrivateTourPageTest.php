<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VietnamPrivateTourPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_vietnam_private_tour_page_is_reachable_from_navigation(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('vietnam'))
            ->assertSee(route('philippines'));

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee(route('vietnam'))
            ->assertSee('Hanoi')
            ->assertSee('Da Nang')
            ->assertSee('Ho Chi Minh');

        $this->get(route('vietnam'))
            ->assertOk()
            ->assertSee('Vietnam Private Tours')
            ->assertSee('A journey through colour.')
            ->assertSee('Where will Vietnam take you?')
            ->assertSee('Hanoi')
            ->assertSee('Ha Long Bay')
            ->assertSee('Da Nang')
            ->assertSee('Hoi An')
            ->assertSee('Ho Chi Minh City')
            ->assertSee('Mekong Delta')
            ->assertSee('role="region" aria-roledescription="carousel" aria-label="Vietnam destinations"', false)
            ->assertSee('aria-label="Next destinations"', false)
            ->assertSee('Enquire on WhatsApp');
    }
}
