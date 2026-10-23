<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChinaMuslimPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_china_muslim_page_shows_the_header_heading_and_footer(): void
    {
        $this->get(route('china-muslim'))
            ->assertOk()
            ->assertSee('class="site-header', false)
            ->assertSee('GALLERY SERIES DEPARTURES')
            ->assertSee('CHINA MUSLIM PACKAGE TOURS')
            ->assertSee('class="site-footer"', false);
    }

    public function test_navbar_china_muslim_link_points_to_the_new_page(): void
    {
        $this->get(route('china-muslim'))
            ->assertOk()
            ->assertSee('href="'.route('china-muslim').'"', false);
    }
}
