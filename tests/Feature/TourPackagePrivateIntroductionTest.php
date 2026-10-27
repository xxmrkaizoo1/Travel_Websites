<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourPackagePrivateIntroductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_tour_introduction_renders_destination_links(): void
    {
        $response = $this->get(route('tour-package-private'));

        $response->assertOk()
            ->assertSee('Travel your way.')
            ->assertSee('A journey planned around')
            ->assertSee('Island & Honeymoon')
            ->assertSee('Indonesia')
            ->assertSee('Thailand')
            ->assertSee('Vietnam')
            ->assertSee('Philippines');
    }

    public function test_introduction_is_listed_once_in_public_and_admin_submenus(): void
    {
        $publicPage = $this->get(route('tour-package-private'))->assertOk();
        $introductionLink = 'href="'.route('tour-package-private').'"';

        $this->assertSame(1, substr_count($publicPage->getContent(), $introductionLink));

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $adminPage = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $this->assertSame(1, substr_count($adminPage->getContent(), 'href="'.route('admin.private-tours.edit').'"'));
        $adminPage->assertSee('aria-label="Tour Package (Private)"', false);
    }
}
