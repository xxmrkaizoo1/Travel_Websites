<?php

namespace Tests\Feature;

use Database\Seeders\LegacyTourPackagePagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcehTourPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_aceh_tour_page_shows_both_four_day_itineraries_and_package_details(): void
    {
        $this->seed(LegacyTourPackagePagesSeeder::class);

        $this->get(route('indonesia'))
            ->assertOk()
            ->assertSee(route('indonesia.aceh'))
            ->assertSee('Show Indonesia destinations', false);

        $this->get(route('indonesia.aceh'))
            ->assertOk()
            ->assertSee('4D3N ACEH TOUR PACKAGE')
            ->assertSee('Aceh City & Beach')
            ->assertSee('Aceh + Sabang')
            ->assertSee('Sultan Iskandar Muda International Airport')
            ->assertSee('Tsunami Mass Grave at Siro Village')
            ->assertSee('Kapal di Atas Rumah')
            ->assertSee('Cut Nyak Dien House')
            ->assertSee('Kilometer 0 Monument of Indonesia')
            ->assertSee('Optional activities such as snorkeling, diving, and glass boat rides')
            ->assertSee('Portola Grand Arabia Hotel')
            ->assertSee('Casa Nemo Beach Resort & Spa')
            ->assertSee('Flight tickets')
            ->assertSee('Private tours and entrance tickets')
            ->assertSee('Aceh attractions')
            ->assertDontSee('attractions slideshow');
    }
}
