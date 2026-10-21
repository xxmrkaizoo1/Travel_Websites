<?php

namespace Tests\Feature;

use App\Models\HomepageService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_service_management(): void
    {
        $this->get(route('admin.homepage.services'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_manage_homepage_services(): void
    {
        $user = User::factory()->create();
        $service = HomepageService::query()->where('slug', 'rest-of-the-world')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.homepage.services'))
            ->assertForbidden();

        $this->put(route('admin.homepage.services.update', $service), [
            'title' => 'Unauthorized service',
        ])->assertForbidden();

        $this->assertDatabaseHas('homepage_services', [
            'id' => $service->id,
            'title' => 'Rest of the World',
        ]);
    }

    public function test_admin_can_edit_service_section_heading_and_card_content(): void
    {
        $admin = $this->createAdmin();
        $service = HomepageService::query()->where('slug', 'rest-of-the-world')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.homepage'))
            ->assertOk()
            ->assertSee('Our services')
            ->assertSee(route('admin.homepage.services'));

        $this->get(route('admin.homepage.services'))
            ->assertOk()
            ->assertSee('Section title')
            ->assertSee('Rest of the World');

        $this->put(route('admin.homepage.services.heading'), [
            'heading' => 'Explore our travel services',
        ])->assertRedirect(route('admin.homepage.services'));

        $this->put(route('admin.homepage.services.update', $service), [
            'title' => 'Worldwide adventures',
            'overlay_label' => "DISCOVER\nTHE WORLD",
            'button_label' => 'Explore tours',
            'href' => 'https://example.com/tours',
            'image_alt' => 'Mountains beside a quiet lake',
        ])->assertRedirect(route('admin.homepage.services.edit', $service));

        $this->assertDatabaseHas('homepage_settings', [
            'key' => 'services_heading',
            'value' => 'Explore our travel services',
        ]);
        $this->assertDatabaseHas('homepage_services', [
            'id' => $service->id,
            'title' => 'Worldwide adventures',
            'overlay_label' => "DISCOVER\nTHE WORLD",
            'button_label' => 'Explore tours',
            'href' => 'https://example.com/tours',
        ]);

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee('Explore our travel services')
            ->assertSee('Worldwide adventures')
            ->assertSee('DISCOVER')
            ->assertSee('THE WORLD')
            ->assertSee('https://example.com/tours');
    }

    public function test_admin_can_replace_service_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $service = HomepageService::query()->where('slug', 'ticketing')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.homepage.services.update', $service), [
                'title' => $service->title,
                'overlay_label' => $service->overlay_label,
                'button_label' => $service->button_label,
                'href' => $service->href,
                'image_alt' => 'A plane crossing a blue sky',
                'image' => UploadedFile::fake()->image('ticketing.webp', 800, 600),
            ])
            ->assertRedirect(route('admin.homepage.services.edit', $service));

        $service->refresh();

        $this->assertDatabaseHas('homepage_services', [
            'id' => $service->id,
            'image_alt' => 'A plane crossing a blue sky',
        ]);
        Storage::disk('public')->assertExists($service->image_path);
    }

    public function test_invalid_service_link_is_rejected_and_not_saved(): void
    {
        $admin = $this->createAdmin();
        $service = HomepageService::query()->where('slug', 'rest-of-the-world')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.homepage.services.edit', $service))
            ->put(route('admin.homepage.services.update', $service), [
                'title' => 'Unsafe destination',
                'overlay_label' => 'WORLD',
                'button_label' => 'Go',
                'href' => 'javascript:alert(1)',
                'image_alt' => 'A destination',
            ])
            ->assertRedirect(route('admin.homepage.services.edit', $service))
            ->assertSessionHasErrors('href');

        $this->assertDatabaseHas('homepage_services', [
            'id' => $service->id,
            'title' => 'Rest of the World',
        ]);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
