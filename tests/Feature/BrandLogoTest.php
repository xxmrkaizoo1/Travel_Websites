<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_smart_muslim_pages_show_the_smart_muslim_logo_and_a_way_back_to_smart_holidays(): void
    {
        foreach (['smart-muslim', 'china-muslim', 'company-trip-mice'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('class="brand-logo" src="'.asset('images/smart-muslim-logo.png').'" alt="Smart Muslim"', false)
                ->assertSee('aria-label="Smart Muslim home"', false)
                ->assertSee('class="smart-holidays-fab" href="'.route('home').'"', false);
        }
    }

    public function test_smart_holidays_pages_keep_the_smart_holidays_logo(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="brand-logo" src="'.asset('images/smart-holidays-logo.png').'" alt="Smart Holidays"', false)
            ->assertSee('alt="Smart Muslim logo"', false);

        $this->get(route('contact'))
            ->assertOk()
            ->assertDontSee('images/smart-muslim-logo.png', false)
            ->assertDontSee('smart-holidays-fab', false);
    }
}
