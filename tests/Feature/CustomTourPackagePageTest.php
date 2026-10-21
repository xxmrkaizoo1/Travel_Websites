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

        $this->assertSame(1, TourPackagePage::query()->where('slug', 'series-departures-2026')->count());

        $this->get(route('tour-package.show', 'series-departures-2026'))->assertOk()
            ->assertSee('Series Departures 2026')
            ->assertSee('Gyeongbokgung')
            ->assertSee('City of Eternal Spring', false)
            ->assertSee('Lake Toba')
            ->assertSee('href="#series-kunming"', false);

        $this->actingAs($this->createAdmin())->get(route('admin.dashboard'))->assertOk()
            ->assertSee('aria-label="Tour Package (Series)"', false)
            ->assertDontSee('Tour Package (Series) <small>Coming soon', false);

        $this->get(route('home'))->assertOk()
            ->assertSee('id="series-tour-menu"', false)
            ->assertSee(route('tour-package.show', 'series-departures-2026').'#series-medan', false);
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
        $this->get(route('tour-package.show', 'japan-series'))->assertOk()->assertSee('series-section', false);

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

    public function test_series_page_editor_shows_destinations_and_saves_without_slides(): void
    {
        $migration = require base_path('database/migrations/2026_10_21_152732_add_series_departures_2026_page.php');
        $migration->up();

        $page = TourPackagePage::query()->where('slug', 'series-departures-2026')->firstOrFail();
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.tour-packages.edit', $page))->assertOk()
            ->assertSee('TOUR PACKAGE (SERIES)', false)
            ->assertSee('3 destinations')
            ->assertSee('series[0][tagline]', false)
            ->assertDontSee('Destination slideshow');

        $payload = $page->only([
            'intro_eyebrow', 'intro_headline', 'intro_highlight', 'intro_link_label', 'intro_body', 'intro_image_alt',
            'intro_image_caption', 'highlights_eyebrow', 'highlights_headline', 'highlights_body', 'highlights',
            'contact_eyebrow', 'contact_headline', 'contact_body', 'contact_cta_label', 'contact_href',
            'meta_title', 'meta_description', 'destinations_eyebrow', 'destinations_headline', 'destinations_body',
            'hero_eyebrow', 'hero_headline', 'hero_highlight', 'hero_body', 'hero_cta_label', 'hero_note', 'hero_image_alt', 'name', 'parent_slug',
        ]);
        $payload['series'] = $page->package_data['series'];
        $payload['series'][0]['tagline'] = 'Edited tagline';

        $this->actingAs($admin)->put(route('admin.tour-packages.update', $page), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tour-packages.edit', $page));

        $series = $page->fresh()->package_data['series'];
        $this->assertCount(3, $series);
        $this->assertSame('Edited tagline', $series[0]['tagline']);
        $this->assertCount(5, $series[0]['gallery']);
        $this->assertSame('images/series/flag-kr.png', $series[0]['flag']);

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
