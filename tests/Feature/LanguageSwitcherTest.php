<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_client_sign_in_pages_offer_english_chinese_and_malay(): void
    {
        foreach (['home', 'smart-muslim', 'contact', 'client.login'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('data-language-switcher', false)
                ->assertSeeInOrder(['data-language="en"', 'data-language="zh-CN"', 'data-language="ms"'], false);
        }
    }
}
