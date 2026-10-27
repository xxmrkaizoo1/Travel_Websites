<?php

namespace Tests\Feature;

use App\Models\GroupToursPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GroupToursPageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_tours_page_shows_focus_tour_cards(): void
    {
        $this->get(route('group-tours'))
            ->assertOk()
            ->assertSee('<title>Group Tours | Smart Holidays by Roystar Travel &amp; Tours</title>', false)
            ->assertSeeInOrder(['GROUP TOURS', 'Focus Tours', 'Malaysia', 'Vietnam', 'Thailand', 'Indonesia', "Can't find your destination?"])
            ->assertSee('<strong>4</strong> destinations', false)
            ->assertSee('Limestone bays, lantern towns and street food.')
            ->assertSee('href="/group-tours/vietnam"', false)
            ->assertSee('Explore tours')
            ->assertSee('href="https://wa.me/60165500590"', false);
    }

    public function test_site_navigation_has_group_tours_dropdown(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['aria-controls="group-tours-menu"', 'href="'.route('group-tours').'"', 'Tour Destinations', 'href="'.route('group-tours.booking-conditions').'"', 'Booking Conditions'], false);
    }

    public function test_booking_conditions_page_lists_each_condition(): void
    {
        $this->get(route('group-tours.booking-conditions'))
            ->assertOk()
            ->assertSee('<title>Booking Conditions | Group Tours | Smart Holidays</title>', false)
            ->assertSeeInOrder(['Booking &amp; deposit', 'Full payment', 'Travel insurance'], false)
            ->assertSee('<li>Deposits are non-refundable and non-transferable.</li>', false);
    }

    public function test_admin_can_update_booking_conditions(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.dashboard'))
            ->assertSeeInOrder(['Tour Destinations', 'href="'.route('admin.group-tours.booking-conditions.edit').'"', 'Booking Conditions'], false);

        $this->get(route('admin.group-tours.booking-conditions.edit'))
            ->assertOk()
            ->assertSee('data-gallery-paged="title"', false);

        $this->put(route('admin.group-tours.booking-conditions.update'), [
            'booking_heading' => 'Terms of Booking',
            'booking_intro' => '',
            'booking_conditions' => [
                ['title' => 'Deposit', 'body' => 'Pay RM 500 per person.
Non-refundable.'],
                ['title' => '', 'body' => ''],
            ],
        ])->assertRedirect(route('admin.group-tours.booking-conditions.edit'))
            ->assertSessionHas('status', 'Booking conditions updated.');

        $this->assertSame([['title' => 'Deposit', 'body' => 'Pay RM 500 per person.
Non-refundable.']], GroupToursPage::query()->firstOrFail()->booking_conditions);

        $this->get(route('group-tours.booking-conditions'))
            ->assertSee('Terms of Booking')
            ->assertSee('<li>Non-refundable.</li>', false)
            ->assertDontSee('Full payment');
    }

    public function test_booking_condition_needs_title_and_text(): void
    {
        $this->actingAs($this->createAdmin())
            ->put(route('admin.group-tours.booking-conditions.update'), [
                'booking_heading' => 'Booking Conditions',
                'booking_conditions' => [['title' => 'Deposit', 'body' => '']],
            ])->assertSessionHasErrors(['booking_conditions.0.body']);
    }

    public function test_guest_and_non_admin_cannot_manage_group_tours_page(): void
    {
        $this->get(route('admin.group-tours.edit'))
            ->assertRedirect(route('login'));
        $this->get(route('admin.group-tours.booking-conditions.edit'))
            ->assertRedirect(route('login'));
        $this->get(route('admin.ground-tours.edit'))
            ->assertRedirect(route('login'));
        $this->get(route('admin.domestic-tours.edit'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->put(route('admin.group-tours.update'), [])
            ->assertForbidden();
        $this->put(route('admin.group-tours.booking-conditions.update'), [])
            ->assertForbidden();
        $this->put(route('admin.ground-tours.update'), [])
            ->assertForbidden();
    }

    public function test_dashboard_lists_group_tours_under_smart_holidays(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['SMART HOLIDAYS', 'href="'.route('admin.group-tours.edit').'"', 'SMART MUSLIM TOUR'], false);
    }

    public function test_admin_can_update_cards_with_upload_and_optional_link(): void
    {
        Storage::fake('public');
        $page = GroupToursPage::query()->firstOrFail();
        $tours = $page->tours;
        unset($tours[3]);
        $tours[] = ['name' => 'Japan', 'image' => '', 'alt' => 'Mount Fuji', 'link_url' => '', 'image_file' => UploadedFile::fake()->image('japan.jpg', 1200, 600)];
        $tours[] = ['name' => '', 'image' => '', 'alt' => '', 'link_url' => ''];

        $this->actingAs($this->createAdmin())
            ->get(route('admin.group-tours.edit'))
            ->assertOk()
            ->assertSee('data-gallery-paged="name"', false);

        $this->put(route('admin.group-tours.update'), $this->payload($page, [
            'heading' => 'Our Group Tours',
            'cta_heading' => 'Planning a company trip?',
            'hero_image' => UploadedFile::fake()->image('banner.jpg', 1600, 700),
            'tours' => $tours,
        ]))->assertRedirect(route('admin.group-tours.edit'))
            ->assertSessionHas('status', 'Group Tours page updated.');

        $saved = $page->fresh()->tours;
        $this->assertCount(4, $saved);
        $this->assertSame('Japan', $saved[3]['name']);
        $this->assertSame('', $saved[3]['link_url']);
        Storage::disk('public')->assertExists($saved[3]['image']);
        Storage::disk('public')->assertExists($page->fresh()->hero_image);

        $this->get(route('group-tours'))
            ->assertOk()
            ->assertSee('Our Group Tours')
            ->assertSee('Planning a company trip?')
            ->assertSee('Japan')
            ->assertSee('href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20group%20tours%20to%20Japan."', false)
            ->assertDontSee('Coastal temple in Bali, Indonesia');
    }

    public function test_admin_can_add_and_clear_banner_video(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $page = GroupToursPage::query()->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.group-tours.update'), $this->payload($page, [
                'hero_video_file' => UploadedFile::fake()->create('clouds.mp4', 2048, 'video/mp4'),
            ]))->assertRedirect(route('admin.group-tours.edit'));

        $video = $page->fresh()->hero_video;
        Storage::disk('public')->assertExists($video);

        $videoTag = '<video class="focus-tours-hero__video" src="'.Storage::disk('public')->url($video).'"';
        $this->get(route('group-tours'))->assertSee('focus-tours-hero has-video', false)->assertSee($videoTag, false);
        $this->get(route('group-tours.booking-conditions'))->assertSee($videoTag, false);

        $this->put(route('admin.group-tours.update'), $this->payload($page->fresh(), ['hero_video' => '']))
            ->assertRedirect(route('admin.group-tours.edit'));

        $this->assertNull($page->fresh()->hero_video);
        Storage::disk('public')->assertMissing($video);
        $this->get(route('group-tours'))->assertDontSee('focus-tours-hero__video', false);
    }

    public function test_banner_video_must_be_mp4_or_webm_from_a_safe_source(): void
    {
        $this->actingAs($this->createAdmin())
            ->put(route('admin.group-tours.update'), $this->payload(GroupToursPage::query()->firstOrFail(), [
                'hero_video' => 'private/secret.mp4',
                'hero_video_file' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ]))->assertSessionHasErrors([
                'hero_video' => 'The video must be a valid http(s) URL or an uploaded video.',
                'hero_video_file' => 'The banner video must be an MP4 or WebM file.',
            ]);
    }

    public function test_page_has_search_and_filter_chips_from_card_tags(): void
    {
        $this->get(route('group-tours'))
            ->assertOk()
            ->assertSee('data-tour-search', false)
            ->assertSeeInOrder(['data-tour-filter="">All', 'data-tour-filter="Domestic">Domestic', 'data-tour-filter="International">International'], false)
            ->assertSee('data-category="Domestic" data-search="malaysia city lights, rainforests and island escapes. domestic"', false);

        $page = GroupToursPage::query()->firstOrFail();
        $tours = $page->tours;
        $tours[1]['category'] = 'Asia';

        $this->actingAs($this->createAdmin())
            ->put(route('admin.group-tours.update'), $this->payload($page, ['tours' => $tours]))
            ->assertRedirect(route('admin.group-tours.edit'));

        $this->assertSame('Asia', $page->fresh()->tours[1]['category']);
        $this->get(route('group-tours'))->assertSee('data-tour-filter="Asia">Asia', false);
    }

    public function test_ground_tours_page_lists_ground_destinations(): void
    {
        $this->get(route('ground-tours'))
            ->assertOk()
            ->assertSee('<title>Ground Tours | Smart Holidays by Roystar Travel &amp; Tours</title>', false)
            ->assertSeeInOrder(['GROUND TOURS', 'Ground Destinations', 'Malaysia', 'China', 'Thailand', 'Korea', 'Japan', 'Indonesia', 'Taiwan', 'Macau', 'Vietnam', 'United Kingdom', 'France', 'Switzerland', 'Australia', 'Need a land package somewhere else?'])
            ->assertSeeInOrder(['data-tour-filter="">All', 'data-tour-filter="Domestic">Domestic', 'data-tour-filter="East Asia">East Asia', 'data-tour-filter="Southeast Asia">Southeast Asia', 'data-tour-filter="Europe">Europe', 'data-tour-filter="Oceania">Oceania'], false)
            ->assertDontSee('data-tour-filter="International"', false)
            ->assertSee('href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20ground%20tours%20to%20Thailand."', false)
            ->assertSee('data-tour-search', false);
    }

    public function test_site_navigation_has_ground_tours_dropdown(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['aria-controls="ground-tours-menu"', 'id="ground-tours-menu"', 'href="'.route('ground-tours').'"', 'Ground Destinations'], false);
    }

    public function test_ground_destinations_menu_lists_each_ground_card(): void
    {
        $ground = GroupToursPage::forKind(GroupToursPage::KIND_GROUND);
        $tours = $ground->tours;
        $tours[array_search('Thailand', array_column($tours, 'name'), true)]['link_url'] = 'https://example.com/thailand';
        $ground->update(['tours' => $tours]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="ground-tour-destinations-menu"',
                'href="'.route('ground-tours').'#tour-malaysia"', 'Malaysia',
                'href="https://example.com/thailand"', 'Thailand',
                'href="'.route('ground-tours').'#tour-vietnam"', 'Vietnam',
                'href="'.route('ground-tours').'#tour-australia"', 'Australia',
            ], false);

        $this->get(route('ground-tours'))->assertSee('id="tour-malaysia"', false);
    }

    public function test_admin_can_update_ground_tours_without_changing_group_tours(): void
    {
        $ground = GroupToursPage::forKind(GroupToursPage::KIND_GROUND);
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertSee('href="'.route('admin.ground-tours.edit').'"', false);

        $this->get(route('admin.ground-tours.edit'))
            ->assertOk()
            ->assertSee('<h1>Ground Tours page</h1>', false)
            ->assertSee('action="'.route('admin.ground-tours.update').'"', false);

        $this->put(route('admin.ground-tours.update'), $this->payload($ground, ['heading' => 'Land Packages']))
            ->assertRedirect(route('admin.ground-tours.edit'))
            ->assertSessionHas('status', 'Ground Tours page updated.');

        $this->assertSame('Land Packages', $ground->fresh()->heading);
        $this->assertSame('Focus Tours', GroupToursPage::forKind(GroupToursPage::KIND_GROUP)->heading);
        $this->get(route('ground-tours'))->assertSee('Land Packages');
    }

    public function test_domestic_tours_page_lists_places_in_malaysia(): void
    {
        $this->get(route('domestic-tours'))
            ->assertOk()
            ->assertSee('<body class="group-tours-page domestic-tours-page">', false)
            ->assertSeeInOrder(['DOMESTIC TOURS · MALAYSIA', 'Explore Malaysia', 'Kuala Lumpur', 'Langkawi', 'Terengganu', 'Sabah', 'Johor'], false)
            ->assertSee('data-tour-filter="Island">Island', false)
            ->assertSee('href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20domestic%20tours%20to%20Langkawi."', false);
    }

    public function test_site_navigation_has_domestic_tour_dropdown_with_malaysia(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['aria-controls="domestic-tours-menu"', 'id="domestic-tours-menu"', 'href="'.route('domestic-tours').'"', 'Malaysia</a>'], false);
    }

    public function test_admin_can_update_domestic_tours_page(): void
    {
        $domestic = GroupToursPage::forKind(GroupToursPage::KIND_DOMESTIC);

        $this->actingAs($this->createAdmin())
            ->get(route('admin.dashboard'))
            ->assertSeeInOrder(['href="'.route('admin.domestic-tours.edit').'"', 'Domestic Tours', 'Malaysia'], false);

        $this->get(route('admin.domestic-tours.edit'))
            ->assertOk()
            ->assertSee('<h1>Domestic Tours page</h1>', false);

        $this->put(route('admin.domestic-tours.update'), $this->payload($domestic, ['heading' => 'Cuti-Cuti Malaysia']))
            ->assertRedirect(route('admin.domestic-tours.edit'))
            ->assertSessionHas('status', 'Domestic Tours page updated.');

        $this->get(route('domestic-tours'))->assertSee('Cuti-Cuti Malaysia');
        $this->assertSame('Focus Tours', GroupToursPage::forKind(GroupToursPage::KIND_GROUP)->heading);
    }

    public function test_card_rejects_unsafe_links_and_missing_images(): void
    {
        $page = GroupToursPage::query()->firstOrFail();
        $tours = $page->tours;
        $tours[0]['link_url'] = 'javascript:alert(1)';
        $tours[1]['image'] = '';

        $this->actingAs($this->createAdmin())
            ->put(route('admin.group-tours.update'), $this->payload($page, ['tours' => $tours]))
            ->assertSessionHasErrors(['tours.0.link_url', 'tours.1.image']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(GroupToursPage $page, array $overrides = []): array
    {
        return [...$page->only(['meta_title', 'meta_description', 'hero_eyebrow', 'heading', 'intro', 'tours', 'cta_heading', 'cta_body', 'cta_label']), ...$overrides];
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
