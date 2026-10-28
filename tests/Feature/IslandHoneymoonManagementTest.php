<?php

namespace Tests\Feature;

use App\Models\IslandDestination;
use App\Models\IslandHoneymoonSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IslandHoneymoonManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_manage_island_honeymoon_content(): void
    {
        $this->get(route('admin.island-honeymoon'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.island-honeymoon.destinations.create'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create();
        $destination = IslandDestination::query()->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.island-honeymoon'))
            ->assertForbidden();

        $this->put(route('admin.island-honeymoon.destinations.update', $destination), [])
            ->assertForbidden();

        $this->assertDatabaseHas('island_destinations', [
            'id' => $destination->id,
            'name' => 'Bali Island',
        ]);
    }

    public function test_admin_can_edit_the_page_content_and_public_page_uses_it(): void
    {
        $admin = $this->createAdmin();
        $settings = IslandHoneymoonSetting::query()->firstOrFail();
        $payload = $settings->toArray();
        unset($payload['id'], $payload['hero_image'], $payload['intro_image'], $payload['created_at'], $payload['updated_at']);
        $payload['intro_body'] = 'Plan a private escape around your favourite island moments.';

        $this->actingAs($admin)
            ->get(route('admin.island-honeymoon'))
            ->assertOk()
            ->assertSee('DESTINATION SPOTLIGHT')
            ->assertSee('name="hero_headline"', false)
            ->assertSee('Add destination');

        $this->put(route('admin.island-honeymoon.update'), $payload)
            ->assertRedirect(route('admin.island-honeymoon'))
            ->assertSessionHas('status', 'Island & Honeymoon page updated successfully.');

        $this->assertDatabaseHas('island_honeymoon_settings', [
            'id' => $settings->id,
            'intro_body' => 'Plan a private escape around your favourite island moments.',
        ]);

        $this->get(route('island-honeymoon'))
            ->assertOk()
            ->assertSee('Plan a private escape around your favourite island moments.');
    }

    public function test_public_page_shows_maafushi_and_krabi_after_bali(): void
    {
        $this->get(route('island-honeymoon'))
            ->assertOk()
            ->assertSeeInOrder(['Bali Island', 'Maafushi Island', 'Krabi Island'])
            ->assertSee('Advantages of Vacationing in Maafushi')
            ->assertSee('Muslim-Friendly &amp; Welcoming Community', false)
            ->assertSee('Advantages of Vacationing in Krabi')
            ->assertSee('Krabi offers a variety of exciting activities like island hopping to Phi Phi and Hong Islands');
    }

    public function test_admin_can_add_edit_and_delete_a_destination_spotlight(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $image = UploadedFile::fake()->image('lagoon.jpg', 1200, 800);
        $payload = $this->destinationData([
            'feature_image' => $image,
            'benefits' => [
                ['icon' => '🌊', 'title' => 'Clear lagoons', 'body' => 'Swim in beautiful blue water.'],
                ['icon' => '', 'title' => '', 'body' => ''],
                ['icon' => '', 'title' => '', 'body' => ''],
                ['icon' => '', 'title' => '', 'body' => ''],
                ['icon' => '', 'title' => '', 'body' => ''],
            ],
            'gallery' => [
                ['image' => 'https://example.com/lagoon.jpg', 'alt' => 'Blue lagoon', 'eyebrow' => 'Explore', 'title' => 'Lagoon days'],
                ['image' => '', 'alt' => '', 'eyebrow' => '', 'title' => ''],
                ['image' => '', 'alt' => '', 'eyebrow' => '', 'title' => ''],
                ['image' => '', 'alt' => '', 'eyebrow' => '', 'title' => ''],
                ['image' => '', 'alt' => '', 'eyebrow' => '', 'title' => ''],
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.island-honeymoon.destinations.create'))
            ->assertOk()
            ->assertSee('Destination story')
            ->assertSee('Slide 1')
            ->assertSee('data-gallery-editor', false)
            ->assertSee('data-add-slide', false)
            ->assertSee('data-remove-slide', false)
            ->assertSee('data-gallery-template', false);

        $this->post(route('admin.island-honeymoon.destinations.store'), $payload)
            ->assertRedirect(route('admin.island-honeymoon'))
            ->assertSessionHas('status', 'Bora Bora destination spotlight added.');

        $destination = IslandDestination::query()->where('name', 'Bora Bora')->firstOrFail();
        $this->assertDatabaseHas('island_destinations', [
            'id' => $destination->id,
            'country' => 'French Polynesia',
        ]);
        $this->assertSame([
            ['icon' => '🌊', 'title' => 'Clear lagoons', 'body' => 'Swim in beautiful blue water.'],
        ], $destination->benefits);
        Storage::disk('public')->assertExists($destination->feature_image);

        $this->get(route('admin.island-honeymoon.destinations.edit', $destination))
            ->assertOk()
            ->assertSee('name="gallery[0][image]"', false)
            ->assertSee('Remove slide');

        $this->get(route('island-honeymoon'))
            ->assertOk()
            ->assertSee('Bora Bora,')
            ->assertSee('Lagoon days');

        $updatedPayload = $this->destinationData([
            'name' => 'Bora Bora Atoll',
            'country' => 'French Polynesia',
            'benefits' => [
                ['icon' => '🌊', 'title' => 'Calm water', 'body' => 'Enjoy a peaceful lagoon escape.'],
            ],
            'gallery' => [
                ['image' => 'https://example.com/atoll.jpg', 'alt' => 'Island atoll', 'eyebrow' => 'Discover', 'title' => 'Atoll escape'],
            ],
        ]);

        $this->put(route('admin.island-honeymoon.destinations.update', $destination), $updatedPayload)
            ->assertRedirect(route('admin.island-honeymoon'))
            ->assertSessionHas('status', 'Bora Bora Atoll destination spotlight updated.');

        $this->assertDatabaseHas('island_destinations', [
            'id' => $destination->id,
            'name' => 'Bora Bora Atoll',
        ]);
        $this->assertSame([
            ['icon' => '🌊', 'title' => 'Calm water', 'body' => 'Enjoy a peaceful lagoon escape.'],
        ], $destination->fresh()->benefits);

        $this->delete(route('admin.island-honeymoon.destinations.destroy', $destination))
            ->assertRedirect(route('admin.island-honeymoon'))
            ->assertSessionHas('status', 'Bora Bora Atoll destination spotlight deleted.');

        $this->assertDatabaseMissing('island_destinations', ['id' => $destination->id]);
        Storage::disk('public')->assertMissing($destination->feature_image);
    }

    public function test_destination_validation_rejects_unsafe_gallery_urls(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->destinationData([
            'feature_image' => UploadedFile::fake()->image('destination.jpg'),
            'gallery' => [
                ['image' => 'javascript:alert(1)', 'alt' => 'Unsafe slide', 'eyebrow' => 'Unsafe', 'title' => 'Unsafe'],
            ],
        ]);

        $this->actingAs($admin)
            ->from(route('admin.island-honeymoon.destinations.create'))
            ->post(route('admin.island-honeymoon.destinations.store'), $payload)
            ->assertRedirect(route('admin.island-honeymoon.destinations.create'))
            ->assertSessionHasErrors('gallery.0.image');

        $this->from(route('admin.island-honeymoon.destinations.create'))
            ->post(route('admin.island-honeymoon.destinations.store'), $this->destinationData([
                'feature_image' => UploadedFile::fake()->image('destination.jpg'),
                'benefits' => 'not-an-array',
                'gallery' => 'not-an-array',
            ]))
            ->assertRedirect(route('admin.island-honeymoon.destinations.create'))
            ->assertSessionHasErrors(['benefits', 'gallery']);

        $this->assertDatabaseMissing('island_destinations', ['name' => 'Bora Bora']);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    private function destinationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Bora Bora',
            'country' => 'French Polynesia',
            'description' => 'Explore calm lagoons and a serene island landscape.',
            'known_as' => 'The Romantic Island',
            'perfect_for' => 'Couples and ocean lovers',
            'benefits_heading' => 'A beautiful island escape',
            'benefits' => [
                ['icon' => '🌊', 'title' => 'Clear lagoons', 'body' => 'Swim in beautiful blue water.'],
            ],
            'feature_image_alt' => 'A tropical island lagoon',
            'feature_caption' => 'Find your island moment',
            'gallery' => [
                ['image' => 'https://example.com/lagoon.jpg', 'alt' => 'Blue lagoon', 'eyebrow' => 'Explore', 'title' => 'Lagoon days'],
            ],
        ], $overrides);
    }
}
