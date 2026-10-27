<?php

namespace Tests\Feature;

use Database\Seeders\IslandHoneymoonPackagePagesSeeder;
use Database\Seeders\LegacyTourPackagePagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Your world of journeys, thoughtfully planned.')
            ->assertSee('Choose how you want to travel.')
            ->assertSee(route('smart-muslim'));
    }

    public function test_smart_muslim_dropdown_links_to_gallery_from_every_page(): void
    {
        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee('href="'.route('gallery').'">Our Gallery</a>', false)
            ->assertSee('href="'.route('contact').'" >Contact Us</a>', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('gallery').'">Our Gallery</a>', false)
            ->assertSee('href="'.route('contact').'" >Contact Us</a>', false);

        $this->get(route('gallery'))
            ->assertOk()
            ->assertSee('<title>Our Gallery | Smart Muslim by Roystar Travel &amp; Tours</title>', false)
            ->assertSee(asset('images/series/seoul-1.jpg'), false);
    }

    public function test_island_honeymoon_page_is_available_from_private_tour_menu(): void
    {
        $this->seed(IslandHoneymoonPackagePagesSeeder::class);
        $this->seed(LegacyTourPackagePagesSeeder::class);

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee(route('island-honeymoon'))
            ->assertSeeInOrder([
                'id="island-destination-menu"',
                'href="'.route('tour-package.show', 'bali').'"',
                'href="'.route('tour-package.show', 'krabi').'"',
                'href="'.route('tour-package.show', 'lombok').'"',
                'href="'.route('tour-package.show', 'maldives').'"',
                'id="indonesia-destination-menu"',
            ], false);

        $this->get(route('island-honeymoon'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="island-destination-menu"',
                'href="'.route('tour-package.show', 'bali').'"',
                'href="'.route('tour-package.show', 'krabi').'"',
                'href="'.route('tour-package.show', 'lombok').'"',
                'href="'.route('tour-package.show', 'maldives').'"',
                'id="indonesia-destination-menu"',
            ], false)
            ->assertSee('Island escapes')
            ->assertSee('honeymoon moments')
            ->assertSee('Bali Island,')
            ->assertSee('Bali destination facts')
            ->assertSee('Island of the Gods')
            ->assertSee('Couples, families')
            ->assertSee('halal-friendly food')
            ->assertSee('budget-friendly homestays')
            ->assertSee('aria-roledescription="carousel"', false)
            ->assertSee('aria-label="Next slide"', false)
            ->assertSee('aria-label="Show slide 5: Time to unwind"', false)
            ->assertSee('Enquire on WhatsApp');
    }

    public function test_homepage_links_to_the_admin_portal_and_smart_muslim_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="site-header smart-holidays-header"', false)
            ->assertSee('alt="Smart Holidays"', false)
            ->assertSee('Group Tours')
            ->assertSee('WhatsApp Us')
            ->assertSee('Book Now')
            ->assertSee(route('login'))
            ->assertSee('class="admin-portal-fab"', false)
            ->assertSee('aria-label="Open admin login"', false)
            ->assertSee(route('smart-muslim'))
            ->assertSee('Smart Muslim Tour')
            ->assertSee('Smart Holidays');
    }

    public function test_indonesia_private_tour_page_is_reachable_from_navigation(): void
    {
        $this->seed(LegacyTourPackagePagesSeeder::class);

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee(route('indonesia'))
            ->assertSee('href="'.route('tour-package.show', 'aceh').'">Aceh</a>', false);

        $this->get(route('island-honeymoon'))
            ->assertOk()
            ->assertSee(route('indonesia'))
            ->assertSee('href="'.route('tour-package.show', 'aceh').'">Aceh</a>', false);

        $this->get(route('indonesia'))
            ->assertOk()
            ->assertSee('aria-label="Show Indonesia destinations"', false)
            ->assertSee('Aceh')
            ->assertSee('Medan')
            ->assertSee('Padang')
            ->assertSee('Surabaya')
            ->assertSee('A world of islands.')
            ->assertSee('One journey, your way.')
            ->assertSee('Where will Indonesia take you?')
            ->assertSee('Yogyakarta')
            ->assertSee('role="region" aria-roledescription="carousel" aria-label="Indonesia destinations"', false)
            ->assertSee('Aceh')
            ->assertSee('Medan')
            ->assertSee('Padang')
            ->assertSee('Surabaya')
            ->assertSee('aria-label="Next destinations"', false)
            ->assertSee('Enquire on WhatsApp');
    }
}
