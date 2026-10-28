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
            ->assertSee('src="'.Storage::disk('public')->url($video).'" poster="'.$page->imageUrl($saved[1]['image']).'" muted loop playsinline preload="none"', false);

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

    public function test_homepage_shows_poster_slideshow_without_journey_thumbnails(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-sh-poster-carousel', false)
            ->assertSeeInOrder(['Trending Packages', 'Kunming &amp; Yunnan', 'Explore Thailand'], false)
            ->assertDontSee('sh-journey-thumbnails', false);
    }

    public function test_homepage_shows_popular_smart_holidays_after_smart_muslim(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['Popular in Smart Muslim', 'Popular in Smart Holidays', 'class="popular-tour-brand">Smart Holidays</em>', 'View all Smart Holidays Tours', 'Trending Packages'], false);
    }

    public function test_admin_can_set_and_remove_popular_smart_holidays_background(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $page = SmartHolidaysPage::query()->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.smart-holidays.update'), $this->payload($page, [
                'holidays_popular_video_file' => UploadedFile::fake()->create('beach.mp4', 2048, 'video/mp4'),
                'holidays_popular_image_file' => UploadedFile::fake()->image('beach.jpg', 1600, 900),
            ]))->assertRedirect(route('admin.smart-holidays.edit'));

        $page->refresh();
        Storage::disk('public')->assertExists([$page->holidays_popular_video, $page->holidays_popular_image]);

        $this->get(route('home'))
            ->assertSee('class="popular-tours popular-tours--holidays has-video"', false)
            ->assertSeeInOrder([
                'class="popular-tours-video" src="'.Storage::disk('public')->url($page->holidays_popular_video).'"',
                'poster="'.Storage::disk('public')->url($page->holidays_popular_image).'"',
            ], false);

        $this->put(route('admin.smart-holidays.update'), $this->payload($page, ['holidays_popular_video' => '', 'holidays_popular_image' => '']))
            ->assertRedirect(route('admin.smart-holidays.edit'));

        Storage::disk('public')->assertMissing([$page->holidays_popular_video, $page->holidays_popular_image]);
        $this->assertNull($page->fresh()->holidays_popular_video);
        $this->get(route('home'))->assertSee('class="popular-tours popular-tours--holidays"', false);
    }

    public function test_popular_smart_holidays_background_image_must_be_safe(): void
    {
        $page = SmartHolidaysPage::query()->firstOrFail();

        $this->actingAs($this->createAdmin())
            ->put(route('admin.smart-holidays.update'), $this->payload($page, [
                'holidays_popular_image' => 'private/secret.jpg',
                'holidays_popular_video_file' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ]))->assertSessionHasErrors([
                'holidays_popular_image' => 'The image must be a valid http(s) URL or an uploaded image.',
                'holidays_popular_video_file' => 'The background video must be an MP4 or WebM file.',
            ]);
    }

    public function test_admin_can_upload_and_remove_posters(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $page = SmartHolidaysPage::query()->firstOrFail();
        $posters = [[
            'image' => '', 'image_file' => UploadedFile::fake()->image('shanghai.jpg', 800, 1000),
            'alt' => 'Shanghai poster', 'label' => '8D7N', 'title' => 'Shanghai Nanxun', 'price' => 'from RM 2,298', 'link_url' => '/china-muslim',
        ]];

        $this->actingAs($admin)
            ->put(route('admin.smart-holidays.update'), $this->payload($page, ['posters_heading' => 'Hot Deals', 'poster_slides' => $posters]))
            ->assertRedirect(route('admin.smart-holidays.edit'));

        $saved = $page->fresh()->poster_slides;
        $this->assertCount(1, $saved);
        Storage::disk('public')->assertExists($saved[0]['image']);

        $this->get(route('home'))
            ->assertSee('Hot Deals')
            ->assertSee('from RM 2,298')
            ->assertDontSee('Explore Thailand');

        $this->put(route('admin.smart-holidays.update'), $this->payload($page->fresh(), ['poster_slides' => []]))
            ->assertRedirect(route('admin.smart-holidays.edit'));

        Storage::disk('public')->assertMissing($saved[0]['image']);
        $this->get(route('home'))->assertDontSee('data-sh-poster-carousel', false);
    }

    public function test_poster_link_must_be_safe(): void
    {
        $page = SmartHolidaysPage::query()->firstOrFail();
        $posters = $page->poster_slides;
        $posters[0]['link_url'] = 'javascript:alert(1)';

        $this->actingAs($this->createAdmin())
            ->put(route('admin.smart-holidays.update'), $this->payload($page, ['poster_slides' => $posters]))
            ->assertSessionHasErrors(['poster_slides.0.link_url' => 'The link must be a site path such as /vietnam or a full http(s) URL.']);

        $this->assertSame($page->poster_slides, $page->fresh()->poster_slides);
    }

    public function test_homepage_shows_short_video_reels_between_brands_and_call_to_action(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-sh-reels', false)
            ->assertSeeInOrder(['id="smart-brands-title"', 'Moments from the road', 'Above the clouds', 'Ottoman domes', 'id="sh-cta-title"'], false);
    }

    public function test_admin_can_upload_and_remove_short_videos(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $page = SmartHolidaysPage::query()->firstOrFail();
        $reels = [[
            'video' => '', 'video_file' => UploadedFile::fake()->create('langkawi.mp4', 2048, 'video/mp4'),
            'image' => '', 'image_file' => UploadedFile::fake()->image('langkawi.jpg', 720, 1280),
            'label' => 'Island escapes', 'title' => 'Langkawi sunset', 'link_url' => '',
        ]];

        $this->actingAs($admin)
            ->put(route('admin.smart-holidays.update'), $this->payload($page, ['reels_heading' => 'Short films', 'reel_slides' => $reels]))
            ->assertRedirect(route('admin.smart-holidays.edit'));

        $saved = $page->fresh()->reel_slides;
        $this->assertCount(1, $saved);
        Storage::disk('public')->assertExists([$saved[0]['video'], $saved[0]['image']]);

        $this->get(route('home'))
            ->assertSee('Short films')
            ->assertSee('src="'.Storage::disk('public')->url($saved[0]['video']).'"', false)
            ->assertDontSee('sh-reel-link', false)
            ->assertDontSee('Above the clouds');

        $this->put(route('admin.smart-holidays.update'), $this->payload($page->fresh(), ['reel_slides' => []]))
            ->assertRedirect(route('admin.smart-holidays.edit'));

        Storage::disk('public')->assertMissing([$saved[0]['video'], $saved[0]['image']]);
        $this->get(route('home'))->assertDontSee('data-sh-reels', false);
    }

    public function test_short_video_must_be_a_safe_video(): void
    {
        $page = SmartHolidaysPage::query()->firstOrFail();
        $reels = $page->reel_slides;
        $reels[0]['video'] = '';
        $reels[0]['video_file'] = UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf');
        $reels[1]['video'] = 'javascript:alert(1)';
        $reels[2]['video'] = '';

        $this->actingAs($this->createAdmin())
            ->put(route('admin.smart-holidays.update'), $this->payload($page, ['reel_slides' => $reels]))
            ->assertSessionHasErrors([
                'reel_slides.0.video_file' => 'Each short video must be an MP4 or WebM file.',
                'reel_slides.1.video' => 'The video must be a valid http(s) URL or an uploaded video.',
                'reel_slides.2.video' => 'Each short video needs a video URL or an uploaded video.',
            ]);

        $this->assertSame($page->reel_slides, $page->fresh()->reel_slides);
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
            'journey_slides', 'posters_eyebrow', 'posters_heading', 'poster_slides', 'reels_eyebrow', 'reels_heading', 'reel_slides', 'popular_eyebrow', 'popular_heading', 'popular_body', 'brands_eyebrow', 'brands_heading',
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
