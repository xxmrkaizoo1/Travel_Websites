<?php

namespace Tests\Feature;

use Database\Seeders\LegacyTourPackagePagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaliTourPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_bali_packages_are_linked_from_indonesia_and_show_itineraries_and_package_details(): void
    {
        $this->seed(LegacyTourPackagePagesSeeder::class);

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee('href="'.route('tour-package.show', 'bali').'"', false)
            ->assertSee('>Bali</a>', false);

        $this->get(route('indonesia'))
            ->assertOk()
            ->assertSee(route('bali'))
            ->assertSee('Explore Bali tour packages');

        $this->get(route('bali'))
            ->assertOk()
            ->assertSee('Bali Tour Packages')
            ->assertSee('Honeymoon')
            ->assertSee('Heaven Gate')
            ->assertSee('Fantastik')
            ->assertSee('Nusa Penida')
            ->assertSee('Aloha Swing')
            ->assertSee('Mount and Lake Batur')
            ->assertSee('Tanah Lot Temple')
            ->assertSee('Lempuyang Temple')
            ->assertSee('Tirta Gangga')
            ->assertSee('Puja Mandala')
            ->assertSee('Broken Beach')
            ->assertSee('Angel’s Billabong')
            ->assertSee('Kelingking Beach')
            ->assertSee('Bintang Bali Resort')
            ->assertSee('Welcome flower garland upon arrival')
            ->assertSee('Price does not include')
            ->assertSee('Bali highlights slideshow')
            ->assertSee('Enquire on WhatsApp');
    }
}
