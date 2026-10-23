<?php

namespace Tests\Feature;

use App\Models\MiceClient;
use App\Models\MiceGalleryImage;
use App\Models\MicePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_shows_seeded_content_and_clients(): void
    {
        $this->get(route('company-trip-mice'))
            ->assertOk()
            ->assertSee('Company Trip')
            ->assertSee('+6017-388 6708')
            ->assertSee('images/clientele/perodua.png', false);
    }

    public function test_admin_can_add_and_remove_gallery_photos(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->get(route('company-trip-mice'))->assertSee('mice-gallery-track', false);

        $this->actingAs($admin)
            ->post(route('admin.mice.gallery.store'), ['caption' => 'Team dinner', 'gallery_image' => UploadedFile::fake()->image('dinner.jpg')])
            ->assertRedirect(route('admin.mice'));

        $photo = MiceGalleryImage::query()->where('caption', 'Team dinner')->firstOrFail();
        Storage::disk('public')->assertExists($photo->image);
        $this->get(route('company-trip-mice'))->assertSee('Team dinner');

        $this->actingAs($admin)->delete(route('admin.mice.gallery.destroy', $photo))->assertRedirect(route('admin.mice'));

        $this->assertModelMissing($photo);
        Storage::disk('public')->assertMissing($photo->image);
    }

    public function test_guests_and_non_admins_cannot_manage_mice_content(): void
    {
        $this->get(route('admin.mice'))->assertRedirect();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.mice.update'), [])
            ->assertForbidden();
    }

    public function test_admin_can_update_page_content(): void
    {
        $page = MicePage::query()->firstOrFail();
        $payload = [
            ...$page->only([
                'hero_eyebrow', 'hero_highlight', 'hero_body', 'hero_cta_label', 'intro_eyebrow', 'intro_heading',
                'intro_highlight', 'intro_body', 'intro_image_alt', 'intro_image_caption', 'why_eyebrow', 'why_heading',
                'highlights', 'contact_eyebrow', 'contact_heading', 'contact_body', 'phone_primary', 'phone_secondary',
                'email', 'whatsapp_number', 'clientele_eyebrow', 'clientele_heading', 'clientele_body',
            ]),
            'hero_title' => 'Corporate Retreats',
        ];

        $this->actingAs($this->createAdmin())
            ->get(route('admin.mice'))
            ->assertOk();

        $this->actingAs($this->createAdmin())
            ->put(route('admin.mice.update'), $payload)
            ->assertRedirect(route('admin.mice'));

        $this->get(route('company-trip-mice'))->assertSee('Corporate Retreats');
    }

    public function test_admin_can_add_and_remove_client_logos(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.mice.clients.store'), [
                'name' => 'New Client Sdn Bhd',
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertRedirect(route('admin.mice'));

        $client = MiceClient::query()->where('name', 'New Client Sdn Bhd')->firstOrFail();
        Storage::disk('public')->assertExists($client->logo);

        $this->actingAs($admin)
            ->delete(route('admin.mice.clients.destroy', $client))
            ->assertRedirect(route('admin.mice'));

        $this->assertModelMissing($client);
        Storage::disk('public')->assertMissing($client->logo);
    }

    public function test_client_logo_must_be_an_image(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.mice.clients.store'), [
                'name' => 'Bad Client',
                'logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('logo');
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
