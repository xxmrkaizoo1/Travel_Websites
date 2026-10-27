<?php

namespace Tests\Feature;

use App\Models\GalleryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryPageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_manage_gallery_page(): void
    {
        $this->get(route('admin.gallery.edit'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->put(route('admin.gallery.update'), [])
            ->assertForbidden();
    }

    public function test_dashboard_lists_gallery_under_smart_muslim_tour(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['SMART MUSLIM TOUR', 'href="'.route('admin.gallery.edit').'"'], false);
    }

    public function test_gallery_page_shows_seeded_content(): void
    {
        $this->get(route('gallery'))
            ->assertOk()
            ->assertSee('<title>Our Gallery | Smart Muslim by Roystar Travel &amp; Tours</title>', false)
            ->assertSee('Smart Muslim Gallery')
            ->assertSee('Flag of South Korea')
            ->assertSee('Waterfall surrounded by forest in Indonesia');
    }

    public function test_admin_can_update_content_add_and_remove_destinations_with_uploads(): void
    {
        Storage::fake('public');
        $page = GalleryPage::query()->firstOrFail();
        $destinations = $page->destinations;
        unset($destinations[3]);
        $destinations[] = [
            'country' => 'Japan',
            'flag_file' => UploadedFile::fake()->image('japan-flag.png', 600, 400),
            'photo_file' => UploadedFile::fake()->image('fuji.jpg', 1200, 800),
            'photo_alt' => 'Mount Fuji at sunrise',
        ];
        $destinations[] = ['country' => '', 'flag' => '', 'photo' => '', 'photo_alt' => ''];

        $this->actingAs($this->createAdmin())
            ->get(route('admin.gallery.edit'))
            ->assertOk()
            ->assertSee('name="hero_title"', false)
            ->assertSee('data-gallery-editor', false)
            ->assertSee('data-gallery-album-preview', false)
            ->assertSee('id="gallery-album"', false);

        $this->put(route('admin.gallery.update'), $this->payload($page, [
            'hero_title' => 'Moments from our journeys',
            'destinations' => $destinations,
        ]))->assertRedirect(route('admin.gallery.edit'))
            ->assertSessionHas('status', 'Smart Muslim gallery updated.');

        $saved = $page->fresh()->destinations;
        $this->assertCount(4, $saved);
        $this->assertSame('Japan', $saved[3]['country']);
        Storage::disk('public')->assertExists($saved[3]['flag']);
        Storage::disk('public')->assertExists($saved[3]['photo']);

        $this->get(route('gallery'))
            ->assertOk()
            ->assertSee('Moments from our journeys')
            ->assertSee('Mount Fuji at sunrise')
            ->assertDontSee('Waterfall surrounded by forest in Indonesia');
    }

    public function test_each_postcard_opens_a_popup_album_with_its_country_photos(): void
    {
        $this->get(route('gallery'))
            ->assertOk()
            ->assertSee('data-gallery-album-open="0"', false)
            ->assertSee('View South Korea photo gallery')
            ->assertSee('id="gallery-album"', false)
            ->assertSee('"caption":"Gyeongbokgung Palace"', false)
            ->assertSee('"caption":"Samosir Island"', false);
    }

    public function test_admin_can_save_popup_album_photos_and_empty_slots_are_ignored(): void
    {
        Storage::fake('public');
        $page = GalleryPage::query()->firstOrFail();
        $destinations = $page->destinations;
        $destinations[0]['photos'] = [
            ['image' => 'images/series/seoul-2.jpg', 'caption' => 'N Seoul Tower'],
            ['image' => '', 'caption' => '', 'image_file' => UploadedFile::fake()->image('bukchon.jpg', 1200, 800)],
            ['image' => '', 'caption' => ''],
        ];
        $destinations[1]['photos'] = [];

        $this->actingAs($this->createAdmin())
            ->put(route('admin.gallery.update'), $this->payload($page, ['destinations' => $destinations]))
            ->assertSessionHasNoErrors();

        $saved = $page->fresh()->destinations;
        $this->assertCount(2, $saved[0]['photos']);
        $this->assertSame('N Seoul Tower', $saved[0]['photos'][0]['caption']);
        Storage::disk('public')->assertExists($saved[0]['photos'][1]['image']);
        $this->assertSame([], $saved[1]['photos']);

        $this->get(route('gallery'))
            ->assertOk()
            ->assertSee('"caption":"N Seoul Tower"', false)
            ->assertSee('"caption":"Traditional Chinese architecture"', false);
    }

    public function test_popup_album_photo_needs_an_image(): void
    {
        $page = GalleryPage::query()->firstOrFail();
        $destinations = $page->destinations;
        $destinations[0]['photos'] = [['image' => '', 'caption' => 'Caption without a photo']];

        $this->actingAs($this->createAdmin())
            ->put(route('admin.gallery.update'), $this->payload($page, ['destinations' => $destinations]))
            ->assertSessionHasErrors('destinations.0.photos.0.image');
    }

    public function test_replacing_an_uploaded_photo_deletes_the_old_file(): void
    {
        Storage::fake('public');
        $page = GalleryPage::query()->firstOrFail();
        $admin = $this->createAdmin();
        $destinations = $page->destinations;
        $destinations[0]['photo_file'] = UploadedFile::fake()->image('first.jpg');

        $this->actingAs($admin)->put(route('admin.gallery.update'), $this->payload($page, ['destinations' => $destinations]));
        $firstPath = $page->fresh()->destinations[0]['photo'];

        $destinations = $page->fresh()->destinations;
        $destinations[0]['photo_file'] = UploadedFile::fake()->image('second.jpg');
        $this->put(route('admin.gallery.update'), $this->payload($page->fresh(), ['destinations' => $destinations]))
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($page->fresh()->destinations[0]['photo']);
    }

    public function test_destination_needs_images_and_rejects_unsafe_image_paths(): void
    {
        $page = GalleryPage::query()->firstOrFail();
        $destinations = $page->destinations;
        $destinations[0]['flag'] = '';
        $destinations[1]['photo'] = '../../.env';

        $this->actingAs($this->createAdmin())
            ->put(route('admin.gallery.update'), $this->payload($page, ['destinations' => $destinations]))
            ->assertSessionHasErrors(['destinations.0.flag', 'destinations.1.photo']);

        $this->assertSame('images/series/flag-kr.png', $page->fresh()->destinations[0]['flag']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(GalleryPage $page, array $overrides = []): array
    {
        return [
            ...$page->only([
                'meta_title', 'meta_description', 'hero_eyebrow', 'hero_title', 'hero_body',
                'moments_eyebrow', 'moments_heading', 'moments_body', 'moments_card_label', 'moments_card_eyebrow', 'destinations',
            ]),
            ...$overrides,
        ];
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
