<?php

namespace Tests\Feature;

use App\Models\TourPackagePage;
use App\Models\User;
use Database\Seeders\IndonesiaTourPackagePagesSeeder;
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
