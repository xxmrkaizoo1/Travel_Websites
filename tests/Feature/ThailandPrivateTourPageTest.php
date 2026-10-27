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
            ->assertSee('aria-label="Show Thailand destinations"', false);

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

    public function test_every_country_destination_card_links_to_its_package_or_an_enquiry(): void
    {
        $this->get(route('thailand'))
            ->assertSeeInOrder(['href="/tour/bangkok"', 'Explore Bangkok tour packages', 'href="/tour/chiang-mai-chiang-rai"', 'href="/tour/pattaya"'], false)
            ->assertSee('href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20Phuket%20tour%20packages." target="_blank" rel="noreferrer">Ask about Phuket packages', false)
            ->assertSee('href="/tour/krabi"', false)
            ->assertSee('href="/tour/kanchanaburi"', false);

        $this->get(route('vietnam'))
            ->assertSeeInOrder(['href="/tour/hanoi"', 'Ask about Ha Long Bay packages', 'href="/tour/da-nang"', 'href="/tour/ho-chi-minh"', 'Ask about Mekong Delta packages'], false);

        $this->get(route('philippines'))
            ->assertSeeInOrder(['href="/tour/boracay"', 'href="/tour/palawan"', 'href="/tour/manila"', 'href="/tour/cebu"', 'Ask about Bohol packages'], false);
    }
}
