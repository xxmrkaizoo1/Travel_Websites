<?php

namespace Tests\Feature;

use App\Models\ChinaMuslimPackage;
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

    public function test_packages_have_search_and_hot_and_duration_filters(): void
    {
        ChinaMuslimPackage::query()->delete();
        ChinaMuslimPackage::factory()->create(['title' => 'Kunming Dali Muslim Tours', 'days' => 9, 'nights' => 7, 'is_hot' => true]);
        ChinaMuslimPackage::factory()->create(['title' => 'Beijing Muslim Tours', 'days' => 7, 'nights' => 5, 'is_hot' => false]);

        $this->get(route('china-muslim'))
            ->assertOk()
            ->assertSee('data-tour-search', false)
            ->assertSeeInOrder(['data-tour-filter="">All', 'data-tour-filter="hot">🔥 Hot', 'data-tour-filter="7d">7 days', 'data-tour-filter="9d">9 days'], false)
            ->assertSee('data-category="hot|9d" data-search="kunming dali muslim tours 9d7n"', false)
            ->assertSee('data-category="7d" data-search="beijing muslim tours 7d5n"', false)
            ->assertSee('2 packages');
    }
}
