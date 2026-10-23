<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_page_shows_country_names_only(): void
    {
        $this->get(route('gallery'))
            ->assertOk()
            ->assertSeeInOrder(['South Korea', 'China', 'Vietnam', 'Indonesia'])
            ->assertDontSee('Seoul, South Korea');
    }

    public function test_smart_muslim_header_links_to_the_gallery_page(): void
    {
        $this->get(route('smart-muslim'))->assertSee(route('gallery'), false);
    }
}
