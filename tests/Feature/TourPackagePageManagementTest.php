<?php

namespace Tests\Feature;

use App\Models\TourPackagePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TourPackagePageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_manage_tour_package_pages(): void
    {
        $this->get(route('admin.tour-packages.edit', 'thailand'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.tour-packages.edit', 'thailand'))
            ->assertForbidden();

        $this->put(route('admin.tour-packages.update', 'thailand'), [])
            ->assertForbidden();
    }

    public function test_dashboard_sidebar_links_to_every_private_tour_package_editor(): void
    {
        $response = $this->actingAs($this->createAdmin())->get(route('admin.dashboard'))->assertOk();

        foreach (TourPackagePage::SLUGS as $slug) {
            $response->assertSee('href="'.route('admin.tour-packages.edit', $slug).'"', false);
        }
    }

    public function test_unknown_tour_package_editor_is_not_found(): void
    {
        $this->actingAs($this->createAdmin())
            ->get('/admin/tour-packages/bali/edit')
            ->assertNotFound();
    }

    public function test_admin_can_edit_text_and_public_page_uses_it(): void
    {
        $admin = $this->createAdmin();
        $page = TourPackagePage::forSlug('vietnam');

        $this->actingAs($admin)
            ->get(route('admin.tour-packages.edit', $page))
            ->assertOk()
            ->assertSee('name="hero_headline"', false)
            ->assertSee('data-gallery-editor', false);

        $this->put(route('admin.tour-packages.update', $page), $this->payload($page, [
            'hero_headline' => 'Lanterns and limestone.',
            'meta_title' => 'Vietnam Escapes | Test',
        ]))->assertRedirect(route('admin.tour-packages.edit', $page))
            ->assertSessionHas('status', 'Vietnam page updated successfully.');

        $this->get(route('vietnam'))
            ->assertOk()
            ->assertSee('Lanterns and limestone.')
            ->assertSee('<title>Vietnam Escapes | Test</title>', false);
    }

    public function test_admin_can_add_edit_upload_and_remove_slides(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $page = TourPackagePage::forSlug('thailand');
        $slides = $this->slidesFor($page);
        $slides[0]['title'] = 'Bangkok nights';
        $slides[1]['image_file'] = UploadedFile::fake()->image('north.jpg', 1200, 800);
        $slides[] = [
            'image' => 'https://example.com/koh-samui.jpg',
            'alt' => 'Koh Samui beach',
            'eyebrow' => 'ISLAND LIFE',
            'title' => 'Koh Samui',
            'body' => 'Slow days by the sea.',
            'link_label' => 'See the island',
            'link_url' => '/island-honeymoon',
        ];
        $slides[] = ['image' => '', 'alt' => '', 'eyebrow' => '', 'title' => '', 'body' => '', 'link_label' => '', 'link_url' => ''];
        $slides = array_slice($slides, 1);

        $this->actingAs($admin)
            ->put(route('admin.tour-packages.update', $page), $this->payload($page, ['slides' => $slides]))
            ->assertRedirect(route('admin.tour-packages.edit', $page));

        $saved = $page->fresh()->slides;

        $this->assertCount(count($slides) - 1, $saved);
        $this->assertSame('Koh Samui', $saved[count($saved) - 1]['title']);
        $this->assertSame('/island-honeymoon', $saved[count($saved) - 1]['link_url']);
        $uploaded = $saved[0]['image'];
        Storage::disk('public')->assertExists($uploaded);

        $this->get(route('thailand'))
            ->assertOk()
            ->assertSee('Koh Samui')
            ->assertSee('See the island')
            ->assertSee('4 of 6', false)
            ->assertDontSee('Bangkok nights');

        $this->put(route('admin.tour-packages.update', $page), $this->payload($page->fresh(), [
            'slides' => array_slice($this->slidesFor($page->fresh()), 1),
        ]))->assertRedirect();

        Storage::disk('public')->assertMissing($uploaded);
    }

    public function test_slide_image_must_be_a_url_or_an_existing_upload(): void
    {
        $page = TourPackagePage::forSlug('philippines');
        $slides = $this->slidesFor($page);
        $slides[0]['image'] = 'private/secret.txt';
        $slides[1]['image'] = '';

        $this->actingAs($this->createAdmin())
            ->from(route('admin.tour-packages.edit', $page))
            ->put(route('admin.tour-packages.update', $page), $this->payload($page, ['slides' => $slides]))
            ->assertRedirect(route('admin.tour-packages.edit', $page))
            ->assertSessionHasErrors(['slides.0.image', 'slides.1.image']);

        $this->assertSame($page->slides, $page->fresh()->slides);
    }

    public function test_slide_link_must_be_a_site_path_or_http_url(): void
    {
        $page = TourPackagePage::forSlug('indonesia');
        $slides = $this->slidesFor($page);
        $slides[1]['link_label'] = 'Click';
        $slides[1]['link_url'] = 'javascript:alert(1)';

        $this->actingAs($this->createAdmin())
            ->from(route('admin.tour-packages.edit', $page))
            ->put(route('admin.tour-packages.update', $page), $this->payload($page, ['slides' => $slides]))
            ->assertSessionHasErrors('slides.1.link_url');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(TourPackagePage $page, array $overrides = []): array
    {
        $data = $page->only([
            'meta_title', 'meta_description',
            'hero_eyebrow', 'hero_headline', 'hero_highlight', 'hero_body', 'hero_cta_label', 'hero_note',
            'intro_eyebrow', 'intro_headline', 'intro_highlight', 'intro_body', 'intro_link_label',
            'intro_image_alt', 'intro_image_caption',
            'highlights_eyebrow', 'highlights_headline', 'highlights_body', 'highlights',
            'destinations_eyebrow', 'destinations_headline', 'destinations_body',
            'contact_eyebrow', 'contact_headline', 'contact_body', 'contact_cta_label', 'contact_href',
        ]);
        $data['slides'] = $this->slidesFor($page);

        return [...$data, ...$overrides];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function slidesFor(TourPackagePage $page): array
    {
        return $page->slides;
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
