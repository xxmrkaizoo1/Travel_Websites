<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyPolicyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_page_is_public(): void
    {
        $this->get(route('privacy-policy'))
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('Information we collect');
    }

    public function test_home_footers_link_to_the_internal_privacy_policy(): void
    {
        $this->get(route('smart-muslim'))->assertSee(route('privacy-policy'), false);
        $this->get(route('home'))->assertSee(route('privacy-policy'), false);
    }
}
