<?php

namespace Tests\Feature;

use App\Models\SmartHolidaysPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SmartHolidaysPageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_manage_smart_holidays_page(): void
    {
        $this->get(route('admin.smart-holidays.edit'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->put(route('admin.smart-holidays.update'), [])
            ->assertForbidden();
    }

    public function test_dashboard_sidebar_has_smart_holidays_category_linking_to_editor(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['SMART HOLIDAYS', 'href="'.route('admin.smart-holidays.edit').'"', 'SMART MUSLIM TOUR'], false);
    }

    public function test_homepage_shows_seeded_content(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<title>Smart Holidays by Roystar Travel &amp; Tours</title>', false)
            ->assertSee('Your world of journeys, thoughtfully planned.')
            ->assertSee('Everything for your next escape.')
            ->assertSeeInOrder(['SMART MUSLIM PACKAGES', 'Popular in Smart Muslim', 'class="popular-tour-brand">Smart Muslim</em>', 'View all Smart Muslim Tours'], false)
            ->assertSee('href="/vietnam">Explore Vietnam', false)
            ->assertSee('href="https://wa.me/60165500590"', false);
    }

    public function test_admin_can_update_content_and_upload_slide_image(): void
    {
        Storage::fake('public');
        $page = SmartHolidaysPage::query()->firstOrFail();
        $slides = $page->journey_slides;
        $slides[0] = [...$slides[0], 'title' => 'Tokyo, Japan', 'image_file' => UploadedFile::fake()->image('tokyo.jpg', 1200, 800)];
        unset($slides[4]);
        $slides[] = array_fill_keys(['image', 'alt', 'eyebrow', 'title', 'body', 'link_label', 'link_url', 'thumb'], '');

        $this->actingAs($this->createAdmin())
            ->get(route('admin.smart-holidays.edit'))
            ->assertOk()
            ->assertSee('name="hero_title"', false)
            ->assertSee('data-gallery-editor', false);

        $this->put(route('admin.smart-holidays.update'), $this->payload($page, [
            'hero_title' => 'Travel further with Smart Holidays.',
            'whatsapp_number' => '60123456789',
            'journey_slides' => $slides,
        ]))->assertRedirect(route('admin.smart-holidays.edit'))
            ->assertSessionHas('status', 'Smart Holidays homepage updated.');

        $saved = $page->fresh()->journey_slides;
        $this->assertCount(4, $saved);
        $this->assertSame('Tokyo, Japan', $saved[0]['title']);
        Storage::disk('public')->assertExists($saved[0]['image']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Travel further with Smart Holidays.')
            ->assertSee('Tokyo, Japan')
            ->assertSee('href="https://wa.me/60123456789"', false)
            ->assertDontSee('Marble Mountains, Vietnam');
    }

    public function test_replacing_an_uploaded_slide_image_deletes_the_old_file(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $page = SmartHolidaysPage::query()->firstOrFail();
        $slides = $page->journey_slides;
        $slides[0]['image_file'] = UploadedFile::fake()->image('first.jpg');
        $this->actingAs($admin)->put(route('admin.smart-holidays.update'), $this->payload($page, ['journey_slides' => $slides]));
        $uploaded = $page->fresh()->journey_slides[0]['image'];

        $this->put(route('admin.smart-holidays.update'), $this->payload($page->fresh(), [
            'journey_slides' => [[...$page->fresh()->journey_slides[0], 'image' => 'images/series/seoul-1.jpg']],
        ]))->assertRedirect(route('admin.smart-holidays.edit'));

        Storage::disk('public')->assertMissing($uploaded);
    }

    public function test_admin_can_upload_and_remove_popular_tours_background_video(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $page = SmartHolidaysPage::query()->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('popular-tours-video', false);

        $this->actingAs($admin)
            ->put(route('admin.smart-holidays.update'), $this->payload($page, [
                'popular_video_file' => UploadedFile::fake()->create('beach.mp4', 2048, 'video/mp4'),
            ]))->assertRedirect(route('admin.smart-holidays.edit'));

        $video = $page->fresh()->popular_video;
        Storage::disk('public')->assertExists($video);

        $editor = $this->get(route('admin.smart-holidays.edit'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/data-video-clear\s*>Clear/', $editor);
        $this->assertMatchesRegularExpression('/data-video-empty\s+hidden\s*>/', $editor);

        $this->get(route('home'))
            ->assertSee('class="popular-tours has-video"', false)
            ->assertSee('src="'.Storage::disk('public')->url($video).'" autoplay muted loop playsinline', false);

        $this->put(route('admin.smart-holidays.update'), $this->payload($page->fresh(), ['popular_video' => '']))
            ->assertRedirect(route('admin.smart-holidays.edit'));

        $this->assertNull($page->fresh()->popular_video);
        Storage::disk('public')->assertMissing($video);
    }

    public function test_admin_can_add_and_remove_a_journey_slide_video(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $page = SmartHolidaysPage::query()->firstOrFail();
        $slides = $page->journey_slides;
        $slides[1]['video_file'] = UploadedFile::fake()->create('istanbul.mp4', 2048, 'video/mp4');

        $this->actingAs($admin)
            ->put(route('admin.smart-holidays.update'), $this->payload($page, ['journey_slides' => $slides]))
            ->assertRedirect(route('admin.smart-holidays.edit'));

        $saved = $page->fresh()->journey_slides;
        $video = $saved[1]['video'];
        Storage::disk('public')->assertExists($video);
        $this->assertSame('', $saved[0]['video']);

        $this->get(route('home'))
            ->assertSee('src="'.Storage::disk('public')->url($video).'" poster="'.$page->imageUrl($saved[1]['image']).'" muted loop playsinline preload="none"', false)
            ->assertSee('class="sh-journey-thumb-video">▶ Video</em>', false);

        $saved[1]['video'] = '';
        $this->put(route('admin.smart-holidays.update'), $this->payload($page->fresh(), ['journey_slides' => $saved]))
            ->assertRedirect(route('admin.smart-holidays.edit'));

        Storage::disk('public')->assertMissing($video);
        $this->get(route('home'))->assertDontSee('data-sh-journey-video', false);
    }

    public function test_background_video_must_be_mp4_or_webm_from_a_safe_source(): void
    {
        $page = SmartHolidaysPage::query()->firstOrFail();

        $this->actingAs($this->createAdmin())
            ->put(route('admin.smart-holidays.update'), $this->payload($page, [
                'popular_video' => 'private/secret.mp4',
                'popular_video_file' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ]))->assertSessionHasErrors([
                'popular_video' => 'The video must be a valid http(s) URL or an uploaded video.',
                'popular_video_file' => 'The background video must be an MP4 or WebM file.',
            ]);
    }

    public function test_slide_link_and_image_must_be_safe(): void
    {
        $page = SmartHolidaysPage::query()->firstOrFail();
        $slides = $page->journey_slides;
        $slides[0]['link_url'] = 'javascript:alert(1)';
        $slides[1]['image'] = 'private/secret.txt';

        $this->actingAs($this->createAdmin())
            ->from(route('admin.smart-holidays.edit'))
            ->put(route('admin.smart-holidays.update'), $this->payload($page, ['journey_slides' => $slides]))
            ->assertRedirect(route('admin.smart-holidays.edit'))
            ->assertSessionHasErrors([
                'journey_slides.0.link_url' => 'The link must be a site path such as /vietnam or a full http(s) URL.',
                'journey_slides.1.image' => 'The image must be a valid http(s) URL or an uploaded image.',
            ]);

        $this->assertSame($page->journey_slides, $page->fresh()->journey_slides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(SmartHolidaysPage $page, array $overrides = []): array
    {
        $data = $page->only([
            'meta_title', 'meta_description', 'hero_eyebrow', 'hero_title', 'hero_body', 'hero_cta_label',
            'hero_secondary_label', 'hero_image_alt', 'stats', 'services_eyebrow', 'services_heading', 'services',
            'journey_slides', 'popular_eyebrow', 'popular_heading', 'popular_body', 'brands_eyebrow', 'brands_heading',
            'cta_heading', 'cta_body', 'cta_label', 'whatsapp_number',
        ]);

        return [...$data, ...$overrides];
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
