<?php

namespace Tests\Feature;

use App\Models\TourPackagePage;
use App\Models\User;
use Database\Seeders\IndonesiaTourPackagePagesSeeder;
use Database\Seeders\IslandHoneymoonPackagePagesSeeder;
use Database\Seeders\LegacyTourPackagePagesSeeder;
use Database\Seeders\ThailandTourPackagePagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomTourPackagePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_create_custom_pages(): void
    {
        $this->get(route('admin.tour-packages.create'))->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.tour-packages.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.tour-packages.store'), ['name' => 'Malaysia', 'kind' => 'package'])->assertForbidden();
        $this->assertDatabaseMissing('tour_package_pages', ['name' => 'Malaysia']);
    }

    public function test_only_admins_can_move_custom_pages(): void
    {
        $page = $this->createCustomPage('Malaysia', 'package');

        $this->put(route('admin.tour-packages.move', $page), ['parent_slug' => 'thailand'])
            ->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.tour-packages.move', $page), ['parent_slug' => 'thailand'])
            ->assertForbidden();

        $this->assertSame(null, $page->fresh()->parent_slug);
    }

    public function test_create_form_preselects_the_requested_kind(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.tour-packages.create', ['kind' => 'package']))
            ->assertOk()
            ->assertSee('value="package" checked', false)
            ->assertDontSee('value="introduction" checked', false);

        $this->actingAs($admin)->get(route('admin.tour-packages.create', ['kind' => 'bogus']))
            ->assertOk()
            ->assertSee('value="introduction" checked', false);
    }

    public function test_dashboard_sidebar_has_context_menu_and_lists_custom_pages(): void
    {
        $page = $this->createCustomPage('Malaysia', 'package');

        $this->actingAs($this->createAdmin())->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-context-menu="tour-package-menu"', false)
            ->assertSee('href="'.route('admin.tour-packages.create', ['kind' => 'introduction']).'"', false)
            ->assertSee('href="'.route('admin.tour-packages.create', ['kind' => 'package']).'"', false)
            ->assertSee('href="'.route('admin.tour-packages.edit', $page).'"', false)
            ->assertSee('data-page-name="Malaysia"', false)
            ->assertSee('data-view-url="'.route('tour-package.show', $page->slug).'"', false)
            ->assertSee('data-delete-url="'.route('admin.tour-packages.destroy', $page).'"', false)
            ->assertSee('data-page-name="Indonesia"', false)
            ->assertSee('data-context-delete', false);
    }

    public function test_creating_an_introduction_page_publishes_it_with_the_navbar_link(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.tour-packages.store'), [
            'name' => 'Malaysia Escapes',
            'kind' => 'introduction',
        ]);

        $page = TourPackagePage::where('slug', 'malaysia-escapes')->firstOrFail();
        $response->assertRedirect(route('admin.tour-packages.edit', $page));
        $this->assertTrue($page->is_custom);
        $this->assertFalse($page->isPackage());

        $this->get('/tour/malaysia-escapes')
            ->assertOk()
            ->assertSee('<title>'.e($page->meta_title).'</title>', false)
            ->assertSee('href="'.route('tour-package.show', 'malaysia-escapes').'"', false);

        $this->get(route('thailand'))->assertSee('href="'.route('tour-package.show', 'malaysia-escapes').'"', false);
    }

    public function test_admin_picks_a_theme_colour_when_creating_and_can_change_it_later(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->assertSee('Choose a theme colour')
            ->assertSeeInOrder(['Ocean Blue', 'Emerald Green', 'Sunset Orange', 'Royal Purple', 'Ruby Red'])
            ->assertSee('name="theme" value="ocean" checked', false);

        $this->post(route('admin.tour-packages.store'), ['name' => 'Kyoto', 'kind' => 'package', 'theme' => 'royal']);
        $page = TourPackagePage::where('slug', 'kyoto')->firstOrFail();

        $this->assertSame('royal', $page->theme);
        $this->get('/tour/kyoto')->assertSee('tour-theme-royal', false);
        $this->get(route('admin.tour-packages.edit', $page))->assertSee('name="theme" value="royal" checked', false);

        $this->put(route('admin.tour-packages.update', $page), [...$this->packagePayload($page), 'theme' => 'sunset'])
            ->assertSessionHasNoErrors();
        $this->assertSame('sunset', $page->fresh()->theme);
        $this->get('/tour/kyoto')->assertSee('tour-theme-sunset', false);

        $this->post(route('admin.tour-packages.store'), ['name' => 'Osaka', 'kind' => 'package', 'theme' => 'neon'])
            ->assertSessionHasErrors('theme');
        $this->post(route('admin.tour-packages.store'), ['name' => 'Nara', 'kind' => 'package']);
        $this->assertSame('ocean', TourPackagePage::where('slug', 'nara')->firstOrFail()->theme);
    }

    public function test_creating_a_package_page_renders_itineraries(): void
    {
        $this->actingAs($this->createAdmin())->post(route('admin.tour-packages.store'), [
            'name' => 'Langkawi Break',
            'kind' => 'package',
        ])->assertRedirect();

        $page = TourPackagePage::where('slug', 'langkawi-break')->firstOrFail();
        $this->assertTrue($page->isPackage());

        $this->get('/tour/langkawi-break')
            ->assertOk()
            ->assertSee('aceh-itinerary-card', false)
            ->assertSee(e($page->package_data['itineraries'][0]['title']), false);
    }

    public function test_indonesia_tour_packages_are_linked_and_render_the_supplied_itineraries(): void
    {
        $this->seed(IndonesiaTourPackagePagesSeeder::class);

        $packages = [
            'yogyakarta' => '4D3N Yogyakarta Borobudur',
            'medan' => '4D3N Medan · Lake Toba · Berastagi',
            'surabaya' => '4D3N Surabaya · Bromo · Batu · Malang',
            'padang' => '4D3N Padang · Bukittinggi',
        ];

        foreach ($packages as $slug => $itineraryTitle) {
            $page = TourPackagePage::forSlug($slug);

            $this->assertTrue($page->is_custom);
            $this->assertSame('indonesia', $page->parent_slug);
            $this->get(route('tour-package.show', $slug))
                ->assertOk()
                ->assertSee($itineraryTitle);
        }

        $indonesia = $this->get(route('indonesia'))->assertOk();

        foreach (array_keys($packages) as $slug) {
            $indonesia->assertSee('href="'.route('tour-package.show', $slug).'"', false);
        }

        $navigation = $this->get(route('smart-muslim'))->assertOk();

        foreach (array_keys($packages) as $slug) {
            $link = 'href="'.route('tour-package.show', $slug).'"';
            $navigation->assertSee($link, false);
            $this->assertSame(1, substr_count($navigation->getContent(), $link));
        }
    }

    public function test_thailand_tour_packages_render_in_english_and_appear_once_in_the_submenu(): void
    {
        $this->seed(ThailandTourPackagePagesSeeder::class);

        $packages = [
            'bangkok' => 'Bangkok 4D3N',
            'pattaya' => 'Bangkok · Pattaya 4D3N',
            'kanchanaburi' => 'Bangkok · Kanchanaburi 4D3N',
            'chiang-mai-chiang-rai' => 'Chiang Mai · Chiang Rai 4D3N',
        ];

        foreach ($packages as $slug => $itineraryTitle) {
            $page = TourPackagePage::forSlug($slug);

            $this->assertSame('thailand', $page->parent_slug);
            $this->get(route('tour-package.show', $slug))
                ->assertOk()
                ->assertSee($itineraryTitle);
        }

        $navigation = $this->get(route('smart-muslim'))->assertOk();

        foreach (array_keys($packages) as $slug) {
            $link = 'href="'.route('tour-package.show', $slug).'"';
            $navigation->assertSee($link, false);
            $this->assertSame(1, substr_count($navigation->getContent(), $link));
        }

        $this->assertStringNotContainsString('href="'.route('thailand').'#destinations">Bangkok</a>', $navigation->getContent());
        $this->assertStringNotContainsString('href="'.route('thailand').'#destinations">Pattaya</a>', $navigation->getContent());
    }

    public function test_page_names_with_the_same_slug_get_unique_addresses(): void
    {
        $admin = $this->createAdmin();

        foreach (range(1, 2) as $ignored) {
            $this->actingAs($admin)->post(route('admin.tour-packages.store'), ['name' => 'Malaysia', 'kind' => 'introduction']);
        }

        $this->assertDatabaseHas('tour_package_pages', ['slug' => 'malaysia']);
        $this->assertDatabaseHas('tour_package_pages', ['slug' => 'malaysia-2']);
    }

    public function test_store_validates_name_and_kind(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.tour-packages.store'), ['name' => '', 'kind' => 'other'])
            ->assertSessionHasErrors(['name', 'kind']);
    }

    public function test_page_created_from_a_parent_is_nested_in_sidebar_and_navbar(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.tour-packages.create', ['parent' => 'indonesia']))
            ->assertOk()
            ->assertSee('name="parent_slug" value="indonesia"', false);

        $this->post(route('admin.tour-packages.store'), ['name' => 'Lombok', 'kind' => 'package', 'parent_slug' => 'indonesia'])
            ->assertRedirect();
        $this->assertDatabaseHas('tour_package_pages', ['slug' => 'lombok', 'parent_slug' => 'indonesia']);

        $this->get(route('admin.dashboard'))
            ->assertSeeInOrder(['data-page-slug="indonesia"', 'data-page-slug="lombok"', 'data-page-slug="thailand"'], false);

        $this->get(route('home'))
            ->assertSeeInOrder(['id="indonesia-destination-menu"', route('tour-package.show', 'lombok'), 'id="thailand-destination-menu"'], false);
    }

    public function test_admin_can_create_a_page_under_island_and_honeymoon(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.tour-packages.create', ['parent' => 'island-honeymoon']))
            ->assertOk()
            ->assertSee('name="parent_slug" value="island-honeymoon"', false)
            ->assertSee('inside Island &amp; Honeymoon', false);

        $this->actingAs($admin)
            ->post(route('admin.tour-packages.store'), [
                'name' => 'Seychelles',
                'kind' => 'package',
                'parent_slug' => 'island-honeymoon',
            ])
            ->assertRedirect(route('admin.tour-packages.edit', 'seychelles'));

        $this->assertDatabaseHas('tour_package_pages', [
            'slug' => 'seychelles',
            'parent_slug' => 'island-honeymoon',
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder([
                'data-page-name="Island &amp; Honeymoon"',
                'data-page-slug="seychelles"',
            ], false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="island-destination-menu"',
                'href="'.route('tour-package.show', 'seychelles').'"',
                'id="indonesia-destination-menu"',
            ], false);
    }

    public function test_admin_can_move_a_page_and_both_navigation_menus_follow_its_new_parent(): void
    {
        $admin = $this->createAdmin();
        $page = $this->createCustomPage('Yogyakarta', 'package');
        $payload = $this->packagePayload($page);
        $payload['parent_slug'] = 'indonesia';

        $this->actingAs($admin)
            ->get(route('admin.tour-packages.edit', $page))
            ->assertOk()
            ->assertSee('name="parent_slug"', false)
            ->assertSee('<option value="indonesia"', false);

        $this->actingAs($admin)
            ->put(route('admin.tour-packages.update', $page), $payload)
            ->assertRedirect(route('admin.tour-packages.edit', $page));

        $this->assertDatabaseHas('tour_package_pages', [
            'slug' => 'yogyakarta',
            'parent_slug' => 'indonesia',
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder([
                'data-page-slug="indonesia"',
                'data-page-slug="yogyakarta"',
                'data-page-slug="thailand"',
            ], false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="indonesia-destination-menu"',
                'href="'.route('tour-package.show', 'yogyakarta').'"',
                'id="thailand-destination-menu"',
            ], false);
    }

    public function test_admin_can_move_a_page_from_the_sidebar_move_endpoint(): void
    {
        $admin = $this->createAdmin();
        $page = $this->createCustomPage('Yogyakarta', 'package');

        $this->actingAs($admin)
            ->putJson(route('admin.tour-packages.move', $page), ['parent_slug' => 'thailand'])
            ->assertOk()
            ->assertJsonPath('parent_slug', 'thailand');

        $this->assertDatabaseHas('tour_package_pages', [
            'slug' => 'yogyakarta',
            'parent_slug' => 'thailand',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="thailand-destination-menu"',
                'href="'.route('tour-package.show', 'yogyakarta').'"',
                'id="vietnam-destination-menu"',
            ], false);
    }

    public function test_sidebar_move_endpoint_rejects_cycles_without_changing_the_page(): void
    {
        $admin = $this->createAdmin();
        $parent = $this->createCustomPage('Central Java', 'package');
        $parent->forceFill(['parent_slug' => 'indonesia'])->save();
        $child = $this->createCustomPage('Yogyakarta', 'package');
        $child->forceFill(['parent_slug' => $parent->slug])->save();

        $this->actingAs($admin)
            ->putJson(route('admin.tour-packages.move', $parent), ['parent_slug' => $child->slug])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('parent_slug');

        $this->assertDatabaseHas('tour_package_pages', [
            'slug' => $parent->slug,
            'parent_slug' => 'indonesia',
        ]);
    }

    public function test_admin_sidebar_exposes_drag_and_tap_move_controls_for_custom_pages(): void
    {
        $page = $this->createCustomPage('Yogyakarta', 'package');

        $this->actingAs($this->createAdmin())->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-navigation-draggable="'.$page->slug.'"', false)
            ->assertSee('data-navigation-select="'.$page->slug.'"', false)
            ->assertSee('data-navigation-parent-target="indonesia"', false)
            ->assertSee('data-navigation-parent-target="island-honeymoon"', false)
            ->assertSee('data-navigation-parent-target=""', false)
            ->assertSee('data-navigation-status', false);
    }

    public function test_admin_cannot_move_a_page_inside_its_own_descendant(): void
    {
        $admin = $this->createAdmin();
        $parent = $this->createCustomPage('Central Java', 'package');
        $parent->forceFill(['parent_slug' => 'indonesia'])->save();
        $child = $this->createCustomPage('Yogyakarta', 'package');
        $child->forceFill(['parent_slug' => $parent->slug])->save();
        $payload = $this->packagePayload($parent);
        $payload['parent_slug'] = $child->slug;

        $this->actingAs($admin)
            ->put(route('admin.tour-packages.update', $parent), $payload)
            ->assertSessionHasErrors('parent_slug');

        $this->assertDatabaseHas('tour_package_pages', [
            'slug' => $parent->slug,
            'parent_slug' => 'indonesia',
        ]);
    }

    public function test_vietnam_has_english_da_nang_and_hanoi_package_pages(): void
    {
        $this->artisan('migrate:refresh', ['--path' => 'database/migrations/2026_10_21_141903_convert_fixed_country_destinations_to_pages.php', '--no-interaction' => true]);
        $fillMigration = require base_path('database/migrations/2026_10_21_142858_fill_vietnam_package_pages.php');
        $fillMigration->up();
        $fillMigration->up();

        $this->get(route('tour-package.show', 'da-nang'))->assertOk()
            ->assertSee('Ba Na Hills Full-Day Tour')
            ->assertSee('Sea Front Hotel')
            ->assertDontSee('HARI 1');
        $this->get(route('tour-package.show', 'hanoi'))->assertOk()
            ->assertSee('Fansipan Legend')
            ->assertSee('Bora Hotel')
            ->assertDontSee('HARI 1');
        $this->assertSame(1, TourPackagePage::query()->where('slug', 'hanoi')->count());
    }

    public function test_series_departures_page_shows_each_destination_and_appears_in_the_navbar(): void
    {
        $migration = require base_path('database/migrations/2026_10_21_152732_add_series_departures_2026_page.php');
        $migration->up();
        $migration->up();
        $popupMigration = require base_path('database/migrations/2026_10_21_202616_add_da_nang_package_popup_content.php');
        $popupMigration->up();
        $kunmingPopupMigration = require base_path('database/migrations/2026_10_21_205604_add_kunming_package_popup_content.php');
        $kunmingPopupMigration->up();
        $chinaPackagesMigration = require base_path('database/migrations/2026_10_23_120000_add_kunming_yunnan_and_beijing_series_packages.php');
        $chinaPackagesMigration->up();
        $chinaPackagesMigration->up();

        $this->assertSame(1, TourPackagePage::query()->where('slug', 'series-departures-2026')->count());

        $series = TourPackagePage::query()->where('slug', 'series-departures-2026')->firstOrFail()->package_data['series'];
        $this->assertSame(1, count(array_filter($series, fn (array $destination): bool => $destination['name'] === 'Kunming – Dali – Lijiang – Shangri-La')));
        $this->assertSame(1, count(array_filter($series, fn (array $destination): bool => $destination['name'] === 'Beijing')));

        $seriesPage = $this->createCustomPage('Japan', 'introduction');
        $seriesPage->forceFill(['parent_slug' => 'series-departures-2026'])->save();
        $nestedPage = $this->createCustomPage('Osaka', 'package');
        $nestedPage->forceFill(['parent_slug' => $seriesPage->slug])->save();

        $this->get(route('tour-package.show', 'series-departures-2026'))->assertOk()
            ->assertSee('data-series-page-slider', false)
            ->assertSee('data-series-page-slide="japan"', false)
            ->assertSee('Discover Japan.', false)
            ->assertSee('Series Departures 2026')
            ->assertSee('Gyeongbokgung')
            ->assertSee('City of Eternal Spring', false)
            ->assertSee('Dragon Bridge')
            ->assertDontSee('Medan')
            ->assertDontSee('Lake Toba')
            ->assertSee('Explore the full Da Nang package')
            ->assertSee('id="da-nang-package-details"', false)
            ->assertSee('Golden Bridge')
            ->assertSee('My Khe Beach')
            ->assertSee('5MDAD-S')
            ->assertSee('Ba Na Hills')
            ->assertSee('RM 2,190')
            ->assertSee('Fully booked')
            ->assertSee('OD502')
            ->assertSee('Chill Palace Hotel')
            ->assertSee('Vietnam Dong (VND)')
            ->assertSee('Sunny season')
            ->assertSee('href="#series-kunming"', false)
            ->assertSee('aria-roledescription="carousel"', false)
            ->assertSee('aria-label="1 of 5"', false)
            ->assertSee('aria-label="Next destination"', false)
            ->assertSee('Destination 1 of 5')
            ->assertSee('Explore the full Seoul package')
            ->assertSee('id="seoul-package-details"', false)
            ->assertSee('6 – 11 Sep 2026')
            ->assertSee('From RM 3,850')
            ->assertSee('Nami Island')
            ->assertSee('20 kg checked baggage')
            ->assertSee('OD820')
            ->assertSee('K-ETA for Malaysians')
            ->assertSee('Please confirm the Seoul hotel details')
            ->assertSee('Explore the full Kunming package')
            ->assertSee('id="kunming-package-details"', false)
            ->assertSee('9MKKMGJ-S')
            ->assertSee('RM 4,850')
            ->assertSee('Shuncheng Mosque')
            ->assertSee('OD670')
            ->assertSee('Days Inn Select Wyndham Airport')
            ->assertSee('Required before arrival')
            ->assertSee('Please confirm Days 8–9 with the tour operator')
            ->assertSee('Kunming & Yunnan tour package')
            ->assertSee('Explore the full Kunming – Dali – Lijiang – Shangri-La package')
            ->assertSee('id="kunming-dali-lijiang-shangri-la-package-details"', false)
            ->assertSee('9MKMG-J')
            ->assertSee('Promo RM3,990')
            ->assertSee('Haicheng Smart Resort Hotel')
            ->assertSee('1 free oxygen cylinder')
            ->assertSee('Explore the full Beijing package')
            ->assertSee('id="beijing-package-details"', false)
            ->assertSee('New Beijing Classic Tour with Universal Studio Beijing')
            ->assertSee('Promo RM 5,990 · Buy 1 Free 1')
            ->assertSee('Forbidden City')
            ->assertSee('MU796')
            ->assertSee('Niujie Mosque & Street')
            ->assertSee('Kaiyuan Hotel')
            ->assertSee('Hoi An Ancient Town')
            ->assertDontSee('Lake Toba');

        $this->actingAs($this->createAdmin())->get(route('admin.dashboard'))->assertOk()
            ->assertSee('aria-label="Tour Package (Series)"', false)
            ->assertSee('data-page-name="Japan"', false)
            ->assertSee('class="admin-nav-link admin-subnav-link"  draggable="true"', false)
            ->assertSee('style="--nest: 1"', false)
            ->assertDontSee('Tour Package (Series) <small>Coming soon', false);

        $this->get(route('home'))->assertOk()
            ->assertSee('id="series-tour-menu"', false)
            ->assertSee(route('tour-package.show', 'series-departures-2026').'#page-japan', false)
            ->assertDontSee(route('tour-package.show', 'japan'), false)
            ->assertDontSee(route('tour-package.show', 'osaka'), false);
    }

    public function test_series_menu_uses_the_page_names_shown_in_the_admin_sidebar(): void
    {
        TourPackagePage::query()->where('slug', 'series-departures-2026')->firstOrFail()->update(['name' => 'Series Departures 2026/27']);
        $seriesPage = $this->createCustomPage('Japan', 'introduction');
        $seriesPage->forceFill(['parent_slug' => 'series-departures-2026'])->save();

        $menu = $this->get(route('home'))->assertOk()->getContent();
        $menu = substr($menu, strpos($menu, 'id="series-tour-menu"'), 1500);

        $this->assertStringContainsString('data-series-page-target="series-departures-2026" aria-current="page">Series Departures 2026/27</a>', $menu);
        $this->assertStringContainsString('data-series-page-target="japan">Japan</a>', $menu);
        $this->assertStringNotContainsString('>Introduction</a>', $menu);

        $this->actingAs($this->createAdmin())->get(route('admin.dashboard'))
            ->assertSee('<span class="admin-subnav-name">Series Departures 2026/27</span>', false);
    }

    public function test_added_and_cleared_series_destinations_update_their_package_slides(): void
    {
        $migration = require base_path('database/migrations/2026_10_21_152732_add_series_departures_2026_page.php');
        $migration->up();

        $page = TourPackagePage::query()->where('slug', 'series-departures-2026')->firstOrFail();
        $kunming = $page->package_data['series'][1];
        $newDestination = [
            'name' => 'Osaka',
            'country' => 'Japan',
            'tagline' => 'Food, culture and city lights.',
            'intro' => 'Explore Osaka with your group.',
            'flag' => '',
            'advantages' => [
                ['icon' => '🍜', 'title' => 'Local food', 'body' => 'Discover Osaka street food.'],
            ],
            'gallery' => [],
        ];
        $page->package_data = ['series' => [$newDestination, $kunming]];
        $page->save();

        $this->get(route('tour-package.show', 'series-departures-2026'))->assertOk()
            ->assertSee('Explore the full Osaka package')
            ->assertSee('id="osaka-package-details"', false)
            ->assertSee('Discover Osaka street food.')
            ->assertDontSee('id="seoul-package-details"', false);

        $this->get(route('home'))->assertOk()
            ->assertSee(route('tour-package.show', 'series-departures-2026').'#series-osaka', false);

        $page->package_data = ['series' => [$kunming]];
        $page->save();

        $this->get(route('tour-package.show', 'series-departures-2026'))->assertOk()
            ->assertDontSee('Osaka')
            ->assertDontSee('id="osaka-package-details"', false)
            ->assertDontSee('id="seoul-package-details"', false)
            ->assertSee('Explore the full Kunming package');
    }

    public function test_existing_medan_series_destination_is_migrated_to_da_nang(): void
    {
        $migration = require base_path('database/migrations/2026_10_21_152732_add_series_departures_2026_page.php');
        $migration->up();

        $page = TourPackagePage::query()->where('slug', 'series-departures-2026')->firstOrFail();
        $series = $page->package_data['series'];
        $series[2]['name'] = 'Medan';
        $series[2]['country'] = 'Indonesia';
        $page->package_data = ['series' => $series];
        $page->meta_description = 'Join our 2026 group series departures to Seoul, Kunming and Medan.';
        $page->hero_note = 'SEOUL · KUNMING · MEDAN';
        $page->save();

        $migration = require base_path('database/migrations/2026_10_21_194923_replace_medan_series_destination_with_danang.php');
        $migration->up();
        $popupMigration = require base_path('database/migrations/2026_10_21_202616_add_da_nang_package_popup_content.php');
        $popupMigration->up();
        $kunmingPopupMigration = require base_path('database/migrations/2026_10_21_205604_add_kunming_package_popup_content.php');
        $kunmingPopupMigration->up();
        $page->refresh();

        $this->assertSame('Da Nang', $page->package_data['series'][2]['name']);
        $this->assertSame('Vietnam', $page->package_data['series'][2]['country']);
        $this->assertSame('images/series/danang-1.jpg', $page->package_data['series'][2]['gallery'][0]['image']);
        $this->assertSame('5MDAD-S', $page->package_data['series'][2]['package_details']['tour_code']);
        $this->assertSame('SEOUL · KUNMING · DA NANG', $page->hero_note);
        $this->assertStringContainsString('Da Nang', $page->meta_description);

        $this->get(route('tour-package.show', $page->slug))->assertOk()
            ->assertSee('Dragon Bridge')
            ->assertSee('href="#series-da-nang"', false)
            ->assertDontSee('Medan');
    }

    public function test_create_form_lets_admin_pick_a_template_for_each_page_kind(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.tour-packages.create'))->assertOk()
            ->assertSee('Series destinations')
            ->assertSee('Compact packages');

        $this->actingAs($admin)->post(route('admin.tour-packages.store'), ['name' => 'Japan Series', 'kind' => 'introduction', 'template' => 'series'])
            ->assertRedirect();

        $series = TourPackagePage::query()->where('slug', 'japan-series')->firstOrFail();
        $this->assertTrue($series->isSeries());
        $this->get(route('tour-package.show', 'japan-series'))->assertOk()
            ->assertSee('series-section', false)
            ->assertSee('images/placeholder-photo.svg', false)
            ->assertSee('Photo coming soon');

        $this->actingAs($admin)->post(route('admin.tour-packages.store'), ['name' => 'Short Trip', 'kind' => 'package', 'template' => 'compact'])
            ->assertRedirect();

        $compact = TourPackagePage::query()->where('slug', 'short-trip')->firstOrFail();
        $this->assertSame('compact', $compact->package_data['template']);
        $this->get(route('tour-package.show', 'short-trip'))->assertOk()
            ->assertSee('Signature package')
            ->assertDontSee('aceh-day-list', false);

        $this->actingAs($admin)->post(route('admin.tour-packages.store'), ['name' => 'Bad Template', 'kind' => 'package', 'template' => 'series'])
            ->assertSessionHasErrors('template');
    }

    public function test_beijing_series_package_uses_brochure_content_and_keeps_uploaded_photos(): void
    {
        foreach (['2026_10_21_152732_add_series_departures_2026_page', '2026_10_23_120000_add_kunming_yunnan_and_beijing_series_packages'] as $migrationName) {
            (require base_path("database/migrations/{$migrationName}.php"))->up();
        }

        $page = TourPackagePage::query()->where('slug', 'series-departures-2026')->firstOrFail();
        $packageData = $page->package_data;
        $beijingIndex = array_search('Beijing', array_column($packageData['series'], 'name'), true);
        $packageData['series'][$beijingIndex]['gallery'] = [['image' => 'tour-packages/beijing.jpg', 'alt' => 'Bai Hai Park', 'caption' => 'Beijing']];
        $page->update(['package_data' => $packageData]);

        (require base_path('database/migrations/2026_10_26_040419_update_beijing_series_package_from_brochure.php'))->up();

        $beijing = $page->fresh()->package_data['series'][$beijingIndex];
        $this->assertSame('5MPKX', $beijing['package_details']['tour_code']);
        $this->assertCount(6, $beijing['package_details']['itinerary']);
        $this->assertContains('Single room supplement', $beijing['package_details']['excludes']);
        $this->assertSame('tour-packages/beijing.jpg', $beijing['gallery'][0]['image']);

        $this->get(route('tour-package.show', 'series-departures-2026'))->assertOk()
            ->assertSee('Trishaw at Old Beijing')
            ->assertSee('5MPKX')
            ->assertSee('Before the winter season it is replaced with 798 Art Zone');
    }

    public function test_melbourne_brochure_replaces_the_placeholder_on_series_departures_2027(): void
    {
        (require base_path('database/migrations/2026_10_21_152732_add_series_departures_2026_page.php'))->up();
        $this->actingAs($this->createAdmin())
            ->post(route('admin.tour-packages.store'), ['name' => 'Series 2027', 'kind' => 'introduction', 'parent_slug' => 'series-departures-2026'])
            ->assertRedirect();
        $this->assertSame('First destination', TourPackagePage::query()->where('slug', 'series-2027')->firstOrFail()->package_data['series'][0]['name']);

        $migration = require base_path('database/migrations/2026_10_26_193932_add_melbourne_to_series_departures_2027.php');
        $migration->up();
        $migration->up();

        $page = TourPackagePage::query()->where('slug', 'series-2027')->firstOrFail();
        $this->assertSame(['Melbourne'], array_column($page->package_data['series'], 'name'));
        $this->assertSame('Series Departures 2027.', $page->hero_highlight);

        $this->get(route('tour-package.show', 'series-2027'))->assertOk()
            ->assertSee('href="#series-melbourne"', false)
            ->assertSee('7MMELS')
            ->assertSee('Now only RM 7,790')
            ->assertSee('22 May 2027')
            ->assertSee('Puffing Billy Steam Train')
            ->assertSee('BI872 · BI005')
            ->assertSee('Why join a series departure?')
            ->assertDontSee('Travel at your pace')
            ->assertDontSee('First destination')
            ->assertDontSee('Plan a private trip to Series 2027');
    }

    public function test_dashboard_tags_the_series_introduction_page_with_its_editable_name(): void
    {
        $migration = require base_path('database/migrations/2026_10_21_152732_add_series_departures_2026_page.php');
        $migration->up();
        TourPackagePage::query()->where('slug', 'series-departures-2026')->update(['name' => 'Series Departures 2027']);

        $this->actingAs($this->createAdmin())->get(route('admin.dashboard'))->assertOk()
            ->assertSee('<span class="admin-subnav-name">Series Departures 2027</span> <small>Intro</small>', false);
    }

    public function test_pages_created_inside_a_series_page_default_to_editable_destination_tabs(): void
    {
        $migration = require base_path('database/migrations/2026_10_21_152732_add_series_departures_2026_page.php');
        $migration->up();
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.tour-packages.create', ['parent' => 'series-departures-2026', 'kind' => 'package']))->assertOk()
            ->assertSee('TOUR PACKAGE (SERIES)', false)
            ->assertSee('value="series" checked', false);

        foreach (['introduction' => 'Japan Intro', 'package' => 'Japan Package'] as $kind => $name) {
            $this->actingAs($admin)->post(route('admin.tour-packages.store'), ['name' => $name, 'kind' => $kind, 'parent_slug' => 'series-departures-2026'])
                ->assertRedirect();

            $page = TourPackagePage::query()->where('name', $name)->firstOrFail();
            $this->assertSame($kind, $page->kind);
            $this->assertTrue($page->isSeries());

            $this->actingAs($admin)->get(route('admin.tour-packages.edit', $page))->assertOk()
                ->assertSee('TOUR PACKAGE (SERIES)', false)
                ->assertSee('series[0][name]', false);
        }

        $this->get(route('tour-package.show', 'series-departures-2026'))->assertOk()
            ->assertSee('series-content-japan-package', false);
    }

    public function test_series_page_editor_shows_destinations_and_saves_without_slides(): void
    {
        $migration = require base_path('database/migrations/2026_10_21_152732_add_series_departures_2026_page.php');
        $migration->up();
        $popupMigration = require base_path('database/migrations/2026_10_21_202616_add_da_nang_package_popup_content.php');
        $popupMigration->up();
        $kunmingPopupMigration = require base_path('database/migrations/2026_10_21_205604_add_kunming_package_popup_content.php');
        $kunmingPopupMigration->up();

        $page = TourPackagePage::query()->where('slug', 'series-departures-2026')->firstOrFail();
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.tour-packages.edit', $page))->assertOk()
            ->assertSee('TOUR PACKAGE (SERIES)', false)
            ->assertSee('5 destinations')
            ->assertSee('series[0][tagline]', false)
            ->assertSee('series[0][package_details][headline]', false)
            ->assertSee('series[1][package_details][headline]', false)
            ->assertSee('series[2][package_details][headline]', false)
            ->assertSee('series[3][package_details][headline]', false)
            ->assertSee('series[4][package_details][headline]', false)
            ->assertSee('Seoul tour package')
            ->assertSee('Kunming & Yunnan tour package')
            ->assertSee('Kunming – Dali – Lijiang – Shangri-La popup package details')
            ->assertSee('Beijing popup package details')
            ->assertSee('Discover Da Nang, Hoi An & Ba Na Hills.')
            ->assertSee('Please confirm the Seoul hotel details with the tour operator before booking.')
            ->assertSee('data-name-prefix="series[0][advantages]"', false)
            ->assertSee('Advantage 1 of 4')
            ->assertSee('aria-label="Advantage card navigation"', false)
            ->assertSee('aria-label="Photo card navigation"', false)
            ->assertSee('aria-label="Departure card navigation"', false)
            ->assertSee('aria-label="Itinerary day card navigation"', false)
            ->assertSee('aria-label="Hotel card navigation"', false)
            ->assertSee('aria-label="Weather season card navigation"', false)
            ->assertSee('data-series-previous', false)
            ->assertSee('data-series-next', false)
            ->assertSee('Add advantage')
            ->assertSee('Add hotel')
            ->assertDontSee('Destination slideshow');

        $payload = $page->only([
            'intro_eyebrow', 'intro_headline', 'intro_highlight', 'intro_link_label', 'intro_body', 'intro_image_alt',
            'intro_image_caption', 'highlights_eyebrow', 'highlights_headline', 'highlights_body', 'highlights',
            'contact_eyebrow', 'contact_headline', 'contact_body', 'contact_cta_label', 'contact_href',
            'meta_title', 'meta_description', 'destinations_eyebrow', 'destinations_headline', 'destinations_body',
            'hero_eyebrow', 'hero_headline', 'hero_highlight', 'hero_body', 'hero_cta_label', 'hero_note', 'hero_image_alt', 'name', 'parent_slug',
        ]);
        $payload['series'] = $page->package_data['series'];
        $packageDetails = $payload['series'][2]['package_details'];
        $packageDetails['includes_text'] = implode("\n", $packageDetails['includes']);
        $packageDetails['excludes_text'] = implode("\n", $packageDetails['excludes']);
        $packageDetails['hotels'] = [[
            'name' => $packageDetails['hotel']['name'],
            'room' => $packageDetails['hotel']['room'],
            'location' => $packageDetails['hotel']['location'],
            'note' => $packageDetails['hotel']['note'],
        ]];
        unset($packageDetails['hotel']);
        unset($packageDetails['includes'], $packageDetails['excludes']);
        $payload['series'][2]['package_details'] = $packageDetails;
        $packageDetails = $payload['series'][0]['package_details'];
        $packageDetails['includes_text'] = implode("\n", $packageDetails['includes']);
        $packageDetails['excludes_text'] = implode("\n", $packageDetails['excludes']);
        $packageDetails['hotels'] = [[
            'name' => $packageDetails['hotel']['name'],
            'room' => $packageDetails['hotel']['room'],
            'location' => $packageDetails['hotel']['location'],
            'note' => $packageDetails['hotel']['note'],
        ]];
        unset($packageDetails['includes'], $packageDetails['excludes']);
        unset($packageDetails['hotel']);
        $payload['series'][0]['package_details'] = $packageDetails;
        $packageDetails = $payload['series'][1]['package_details'];
        $packageDetails['includes_text'] = implode("\n", $packageDetails['includes']);
        $packageDetails['excludes_text'] = implode("\n", $packageDetails['excludes']);
        unset($packageDetails['includes'], $packageDetails['excludes']);
        unset($packageDetails['hotel']);
        $payload['series'][1]['package_details'] = $packageDetails;
        foreach ([3, 4] as $index) {
            $packageDetails = $payload['series'][$index]['package_details'];
            $packageDetails['includes_text'] = implode("\n", $packageDetails['includes']);
            $packageDetails['excludes_text'] = implode("\n", $packageDetails['excludes']);
            unset($packageDetails['includes'], $packageDetails['excludes']);
            $payload['series'][$index]['package_details'] = $packageDetails;
        }
        $payload['series'][0]['tagline'] = 'Edited tagline';
        array_pop($payload['series'][0]['advantages']);
        $payload['series'][0]['advantages'][] = ['icon' => '✨', 'title' => 'Added advantage', 'body' => 'This repeated card was added from the editor.'];

        $this->actingAs($admin)->put(route('admin.tour-packages.update', $page), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tour-packages.edit', $page));

        $series = $page->fresh()->package_data['series'];
        $this->assertCount(5, $series);
        $this->assertSame('Edited tagline', $series[0]['tagline']);
        $this->assertCount(5, $series[0]['gallery']);
        $this->assertCount(4, $series[0]['advantages']);
        $this->assertSame('Added advantage', $series[0]['advantages'][3]['title']);
        $this->assertSame('images/series/flag-kr.png', $series[0]['flag']);
        $this->assertSame('6MICN', $series[0]['package_details']['tour_code']);
        $this->assertCount(6, $series[0]['package_details']['itinerary']);
        $this->assertContains('Return Batik Air flight tickets', $series[0]['package_details']['includes']);
        $this->assertSame('9MKKMGJ-S', $series[1]['package_details']['tour_code']);
        $this->assertCount(9, $series[1]['package_details']['itinerary']);
        $this->assertCount(6, $series[1]['package_details']['hotels']);
        $this->assertContains('Return Batik Air flight tickets', $series[2]['package_details']['includes']);
        $this->assertSame('9MKMG-J', $series[3]['package_details']['tour_code']);
        $this->assertCount(9, $series[3]['package_details']['itinerary']);
        $this->assertContains('1 free oxygen cylinder', $series[3]['package_details']['includes']);
        $this->assertSame('New Beijing Classic Tour with Universal Studio Beijing', $series[4]['package_details']['headline']);
        $this->assertCount(6, $series[4]['package_details']['itinerary']);

        $payload['series'][0]['package_details']['headline'] = 'Updated Seoul package';
        $payload['series'][0]['package_details']['itinerary'][0]['places'] = 'Updated Seoul arrival details.';
        $payload['series'][1]['package_details']['headline'] = 'Updated Kunming package';
        $payload['series'][1]['package_details']['departures'][0]['price'] = 'RM 4,999';
        $payload['series'][1]['package_details']['itinerary'][7]['title'] = 'Updated Kunming Day 8';
        $payload['series'][1]['package_details']['hotels'][0]['name'] = 'Updated Kunming hotel';
        $payload['series'][2]['package_details']['headline'] = 'Updated Da Nang package';
        $payload['series'][2]['package_details']['departures'][0]['price'] = 'RM 1,999';
        $payload['series'][2]['package_details']['itinerary'][0]['title'] = 'Updated arrival day';
        $payload['series'][2]['package_details']['includes_text'] = "Return flights\nAirport transfers";

        $this->actingAs($admin)->put(route('admin.tour-packages.update', $page), $payload)
            ->assertSessionHasNoErrors();

        $seoulDetails = $page->fresh()->package_data['series'][0]['package_details'];
        $this->assertSame('Updated Seoul package', $seoulDetails['headline']);
        $this->assertSame('Updated Seoul arrival details.', $seoulDetails['itinerary'][0]['places']);
        $kunmingDetails = $page->fresh()->package_data['series'][1]['package_details'];
        $this->assertSame('Updated Kunming package', $kunmingDetails['headline']);
        $this->assertSame('RM 4,999', $kunmingDetails['departures'][0]['price']);
        $this->assertSame('Updated Kunming Day 8', $kunmingDetails['itinerary'][7]['title']);
        $this->assertSame('Updated Kunming hotel', $kunmingDetails['hotels'][0]['name']);
        $daNangDetails = $page->fresh()->package_data['series'][2]['package_details'];
        $this->assertSame('Updated Da Nang package', $daNangDetails['headline']);
        $this->assertSame('RM 1,999', $daNangDetails['departures'][0]['price']);
        $this->assertSame('Updated arrival day', $daNangDetails['itinerary'][0]['title']);
        $this->assertSame(['Return flights', 'Airport transfers'], $daNangDetails['includes']);
        $this->get(route('tour-package.show', $page->slug))
            ->assertOk()
            ->assertSee('Updated Seoul package')
            ->assertSee('Updated Seoul arrival details.')
            ->assertSee('Added advantage')
            ->assertDontSee('Shopping Paradise')
            ->assertSee('Updated Kunming package')
            ->assertSee('RM 4,999')
            ->assertSee('Updated Kunming Day 8')
            ->assertSee('Updated Kunming hotel')
            ->assertSee('Updated Da Nang package')
            ->assertSee('RM 1,999')
            ->assertSee('Updated arrival day')
            ->assertSee('Airport transfers')
            ->assertSee('Kunming – Dali – Lijiang – Shangri-La tour package')
            ->assertSee('New Beijing Classic Tour with Universal Studio Beijing');

        $payload['series'][2]['package_details']['price_from'] = str_repeat('x', 81);
        $this->actingAs($admin)->put(route('admin.tour-packages.update', $page), $payload)
            ->assertSessionHasErrors('series.2.package_details.price_from');

        $payload['series'][2]['package_details']['price_from'] = 'From RM 1,890';
        $payload['series'][3] = [
            'name' => 'Tokyo', 'country' => 'Japan', 'tagline' => 'Neon and temples.', 'intro' => 'Tokyo intro.', 'flag' => '',
            'advantages' => [['icon' => '', 'title' => 'Easy', 'body' => 'Everything arranged.'], ['icon' => '', 'title' => '', 'body' => '']],
            'gallery' => [['image' => '', 'alt' => '', 'caption' => '']],
        ];
        $payload['series'][4] = ['name' => '', 'country' => '', 'tagline' => '', 'intro' => '', 'flag' => ''];

        $this->actingAs($admin)->put(route('admin.tour-packages.update', $page), $payload)
            ->assertSessionHasNoErrors();

        $series = $page->fresh()->package_data['series'];
        $this->assertCount(4, $series);
        $this->assertSame('Tokyo', $series[3]['name']);
        $this->assertCount(1, $series[3]['advantages']);

        $this->get(route('home'))->assertSee('#series-tokyo', false);

        $payload['series'][3]['name'] = '';
        $payload['series'][3]['country'] = '';

        $this->actingAs($admin)->put(route('admin.tour-packages.update', $page), $payload)
            ->assertSessionHasNoErrors();

        $this->assertCount(3, $page->fresh()->package_data['series']);
        $this->get(route('home'))->assertDontSee('#series-tokyo', false);
        $this->get(route('tour-package.show', $page->slug))->assertDontSee('Tokyo intro.');
    }

    public function test_country_destinations_are_editable_pages_shared_by_admin_sidebar_and_public_navbar(): void
    {
        $this->artisan('migrate:refresh', ['--path' => 'database/migrations/2026_10_21_141903_convert_fixed_country_destinations_to_pages.php', '--no-interaction' => true]);
        $admin = $this->createAdmin();
        $expected = ['hanoi', 'da-nang', 'ho-chi-minh', 'hoi-an', 'manila', 'boracay', 'palawan', 'cebu'];

        $publicNavigation = $this->get(route('home'))->assertOk();
        $adminSidebar = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        foreach ($expected as $slug) {
            $this->assertDatabaseHas('tour_package_pages', ['slug' => $slug, 'is_custom' => true]);
            $publicNavigation->assertSee('href="'.route('tour-package.show', $slug).'"', false);
            $adminSidebar->assertSee('href="'.route('admin.tour-packages.edit', $slug).'"', false);
            $adminSidebar->assertSee('data-navigation-draggable="'.$slug.'"', false);
        }
    }

    public function test_public_tour_list_stays_in_sync_with_admin_managed_pages(): void
    {
        $pages = [
            $this->createCustomPage('Egypt', 'introduction'),
            $this->createCustomPage('Turkiye', 'introduction'),
        ];
        $admin = $this->createAdmin();

        $publicNavigation = $this->get(route('home'))->assertOk();
        $adminSidebar = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        foreach ($pages as $page) {
            $publicLink = 'href="'.route('tour-package.show', $page->slug).'"';

            $publicNavigation->assertSee($publicLink, false);
            $adminSidebar->assertSee('data-page-name="'.$page->name.'"', false)
                ->assertSee('data-view-url="'.route('tour-package.show', $page->slug).'"', false);

            $this->assertSame(1, substr_count($publicNavigation->getContent(), $publicLink));
        }
    }

    public function test_navigation_nests_locations_under_their_country_and_parent_destination(): void
    {
        $region = $this->createCustomPage('Central Java', 'introduction');
        $region->forceFill(['parent_slug' => 'indonesia'])->save();

        $city = $this->createCustomPage('Yogyakarta', 'package');
        $city->forceFill(['parent_slug' => $region->slug])->save();

        $navigation = $this->get(route('home'))->assertOk();

        $navigation->assertSeeInOrder([
            'id="indonesia-destination-menu"',
            'href="'.route('tour-package.show', $region->slug).'"',
            'id="'.$region->slug.'-destination-menu"',
            'href="'.route('tour-package.show', $city->slug).'"',
            'id="thailand-destination-menu"',
        ], false);
        $this->assertSame(1, substr_count($navigation->getContent(), 'href="'.route('tour-package.show', $city->slug).'"'));
    }

    public function test_legacy_destinations_appear_under_their_parent_in_navigation(): void
    {
        foreach (['Aceh', 'Bali', 'Bangkok', 'Krabi', 'Lombok', 'Maldives'] as $destination) {
            $this->createCustomPage($destination, 'package');
        }

        $legacyDestinationsMigration = require base_path('database/migrations/2026_10_21_090633_assign_legacy_tour_destinations_to_their_parents.php');
        $legacyDestinationsMigration->up();
        $countryDestinationsMigration = require base_path('database/migrations/2026_10_21_090830_move_island_destinations_under_their_countries.php');
        $countryDestinationsMigration->up();
        $islandHoneymoonDestinationsMigration = require base_path('database/migrations/2026_10_21_112700_move_island_destinations_to_island_honeymoon.php');
        $islandHoneymoonDestinationsMigration->up();

        $this->assertDatabaseHas('tour_package_pages', ['slug' => 'aceh', 'parent_slug' => 'indonesia']);
        $this->assertDatabaseHas('tour_package_pages', ['slug' => 'bali', 'parent_slug' => 'island-honeymoon']);
        $this->assertDatabaseHas('tour_package_pages', ['slug' => 'bangkok', 'parent_slug' => 'thailand']);
        $this->assertDatabaseHas('tour_package_pages', ['slug' => 'krabi', 'parent_slug' => 'island-honeymoon']);
        $this->assertDatabaseHas('tour_package_pages', ['slug' => 'lombok', 'parent_slug' => 'island-honeymoon']);
        $this->assertDatabaseHas('tour_package_pages', ['slug' => 'maldives', 'parent_slug' => 'island-honeymoon']);

        $homeNavigation = $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="island-destination-menu"',
                'href="'.route('tour-package.show', 'bali').'"',
                'href="'.route('tour-package.show', 'krabi').'"',
                'href="'.route('tour-package.show', 'lombok').'"',
                'href="'.route('tour-package.show', 'maldives').'"',
                'id="indonesia-destination-menu"',
                'href="'.route('tour-package.show', 'aceh').'"',
                'id="thailand-destination-menu"',
                'href="'.route('tour-package.show', 'bangkok').'"',
            ], false);

        foreach (['bali', 'krabi', 'lombok', 'maldives'] as $slug) {
            $this->assertSame(1, substr_count($homeNavigation->getContent(), 'href="'.route('tour-package.show', $slug).'"'));
        }

        $this->assertStringNotContainsString('id="maldives-destination-menu"', $homeNavigation->getContent());
    }

    public function test_shared_navigation_renders_on_all_public_tour_pages(): void
    {
        $this->seed([
            LegacyTourPackagePagesSeeder::class,
            IslandHoneymoonPackagePagesSeeder::class,
            IndonesiaTourPackagePagesSeeder::class,
            ThailandTourPackagePagesSeeder::class,
        ]);

        $pages = [
            route('home'),
            route('smart-muslim'),
            route('tour-package-private'),
            route('island-honeymoon'),
            route('indonesia'),
            route('indonesia.aceh'),
            route('bali'),
            route('thailand'),
            route('vietnam'),
            route('philippines'),
            route('tour-package.show', 'bangkok'),
        ];

        foreach ($pages as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('id="primary-nav"', false)
                ->assertSee(route('smart-muslim'))
                ->assertSee(route('tour-package-private'));
        }
    }

    public function test_store_rejects_an_unknown_parent(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.tour-packages.store'), ['name' => 'Lombok', 'kind' => 'package', 'parent_slug' => 'nowhere'])
            ->assertSessionHasErrors('parent_slug');
    }

    public function test_deleting_a_parent_moves_its_children_up_a_level(): void
    {
        $parent = $this->createCustomPage('Java', 'introduction');
        $parent->forceFill(['parent_slug' => 'indonesia'])->save();
        $child = $this->createCustomPage('Bromo', 'package');
        $child->forceFill(['parent_slug' => 'java'])->save();

        $this->actingAs($this->createAdmin())->delete(route('admin.tour-packages.destroy', $parent));

        $this->assertSame('indonesia', $child->fresh()->parent_slug);
    }

    public function test_built_in_and_unknown_slugs_are_not_served_under_the_tour_prefix(): void
    {
        $this->get('/tour/thailand')->assertNotFound();
        $this->get('/tour/does-not-exist')->assertNotFound();
    }

    public function test_package_editor_shows_repeaters_and_saves_changes(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $page = $this->createCustomPage('Malaysia', 'package');

        $this->actingAs($admin)->get(route('admin.tour-packages.edit', $page))
            ->assertOk()
            ->assertSee('data-repeater', false)
            ->assertSee('name="package[itineraries][0][days][0][title]"', false)
            ->assertSee('Delete this page');

        $payload = $this->packagePayload($page);
        $payload['name'] = 'Malaysia Deluxe';
        $payload['package']['itineraries'][0]['title'] = 'Kuala Lumpur and Langkawi';
        $dayCount = count($payload['package']['itineraries'][0]['days']) + 1;
        $payload['package']['itineraries'][0]['days'][] = ['title' => 'Departure', 'body' => 'Fly home.', 'meals' => ''];
        $payload['package']['itineraries'][] = ['duration' => '', 'title' => '', 'summary' => '', 'cta_label' => '', 'cta_url' => '', 'days' => [['title' => '', 'body' => '']]];
        $payload['package']['gallery'] = [
            ['image' => '', 'image_file' => UploadedFile::fake()->image('beach.jpg', 1200, 800), 'alt' => 'Beach', 'eyebrow' => 'ISLAND', 'title' => 'Pantai Cenang'],
        ];

        $this->put(route('admin.tour-packages.update', $page), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tour-packages.edit', $page));

        $page->refresh();
        $this->assertSame('Malaysia Deluxe', $page->name);
        $this->assertSame('malaysia', $page->slug);
        $this->assertCount(1, $page->package_data['itineraries']);
        $this->assertCount($dayCount, $page->package_data['itineraries'][0]['days']);
        Storage::disk('public')->assertExists($page->package_data['gallery'][0]['image']);

        $this->get('/tour/malaysia')->assertSee('Kuala Lumpur and Langkawi')->assertSee('Pantai Cenang');
    }

    public function test_package_update_requires_a_valid_itinerary_link(): void
    {
        $page = $this->createCustomPage('Malaysia', 'package');
        $payload = $this->packagePayload($page);
        $payload['package']['itineraries'][0]['cta_url'] = 'javascript:alert(1)';

        $this->actingAs($this->createAdmin())
            ->put(route('admin.tour-packages.update', $page), $payload)
            ->assertSessionHasErrors('package.itineraries.0.cta_url');
    }

    public function test_deleting_a_custom_page_removes_it_and_its_images(): void
    {
        Storage::fake('public');
        $page = $this->createCustomPage('Malaysia', 'introduction');
        Storage::disk('public')->put('tour-packages/hero.jpg', 'x');
        $page->update(['hero_image' => 'tour-packages/hero.jpg']);

        $this->actingAs($this->createAdmin())
            ->delete(route('admin.tour-packages.destroy', $page))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertModelMissing($page);
        Storage::disk('public')->assertMissing('tour-packages/hero.jpg');
        $this->get('/tour/malaysia')->assertNotFound();
    }

    public function test_built_in_pages_cannot_be_deleted(): void
    {
        $this->actingAs($this->createAdmin())
            ->delete(route('admin.tour-packages.destroy', TourPackagePage::forSlug('thailand')))
            ->assertForbidden();

        $this->assertDatabaseHas('tour_package_pages', ['slug' => 'thailand']);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    private function createCustomPage(string $name, string $kind): TourPackagePage
    {
        $page = new TourPackagePage(TourPackagePage::starterContent($kind, $name));
        $page->slug = strtolower($name);
        $page->is_custom = true;
        $page->save();

        return $page;
    }

    /**
     * @return array<string, mixed>
     */
    private function packagePayload(TourPackagePage $page): array
    {
        return [
            ...$page->only([
                'meta_title', 'meta_description', 'hero_eyebrow', 'hero_headline', 'hero_highlight', 'hero_body',
                'hero_cta_label', 'hero_note', 'intro_eyebrow', 'intro_headline', 'intro_highlight', 'intro_body',
                'intro_link_label', 'intro_image_alt', 'intro_image_caption', 'highlights_eyebrow',
                'highlights_headline', 'highlights_body', 'contact_eyebrow', 'contact_headline', 'contact_body',
                'contact_cta_label', 'contact_href',
            ]),
            'name' => $page->name,
            'highlights' => $page->highlights,
            'package' => $page->package_data,
        ];
    }
}
