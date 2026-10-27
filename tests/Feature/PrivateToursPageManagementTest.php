<?php

namespace Tests\Feature;

use App\Models\PrivateToursPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateToursPageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_shows_highlights_and_destination_cards(): void
    {
        $this->get(route('tour-package-private'))
            ->assertOk()
            ->assertSee('<title>Private Tour Packages | Smart Muslim by Roystar Travel &amp; Tours</title>', false)
            ->assertSee('src="'.e(PrivateToursPage::DEFAULT_HERO_IMAGE).'"', false)
            ->assertSeeInOrder(['Travel your way.', 'Make it meaningful.', 'Halal meals', 'Prayer times planned in', 'A journey planned around', 'Explore our destinations.'])
            ->assertSeeInOrder(['href="/island-honeymoon"', 'Island &amp; Honeymoon', 'href="/indonesia"', 'href="/thailand"', 'href="/vietnam"', 'href="/philippines"', 'Explore the Philippines'], false)
            ->assertSee('5 destinations');
    }

    public function test_guests_cannot_open_the_editor(): void
    {
        $this->get(route('admin.private-tours.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.private-tours.update'), [])->assertRedirect(route('login'));
    }

    public function test_admin_can_update_text_highlights_and_destinations(): void
    {
        $page = PrivateToursPage::query()->firstOrFail();

        $this->actingAs($this->createAdmin())
            ->get(route('admin.private-tours.edit'))
            ->assertOk()
            ->assertSee('<h1>Private tours introduction</h1>', false)
            ->assertSee('action="'.route('admin.private-tours.update').'"', false);

        $this->put(route('admin.private-tours.update'), $this->payload($page, [
            'hero_title' => 'Your trip, your rules.',
            'perks' => [['icon' => '⭐', 'title' => 'Five-star stays', 'body' => ''], ['icon' => '', 'title' => '', 'body' => '']],
            'destinations' => [
                ['name' => 'Japan', 'tagline' => 'Halal ramen in Tokyo.', 'link_url' => '/tour/japan', 'link_label' => '', 'image' => 'https://example.com/japan.jpg', 'alt' => 'Mount Fuji'],
                ['name' => '', 'tagline' => '', 'link_url' => '', 'link_label' => '', 'image' => '', 'alt' => ''],
            ],
        ]))->assertRedirect(route('admin.private-tours.edit'))
            ->assertSessionHas('status', 'Private tours page updated.');

        $page->refresh();
        $this->assertSame([['icon' => '⭐', 'title' => 'Five-star stays', 'body' => '']], $page->perks);
        $this->assertCount(1, $page->destinations);

        $this->get(route('tour-package-private'))
            ->assertSee('Your trip, your rules.')
            ->assertSee('Five-star stays')
            ->assertDontSee('Halal meals')
            ->assertSeeInOrder(['href="/tour/japan"', 'Japan', 'Halal ramen in Tokyo.', 'Explore'], false)
            ->assertSee('1 destination');
    }

    public function test_uploaded_banner_replaces_the_default_and_clearing_restores_it(): void
    {
        Storage::fake('public');
        $page = PrivateToursPage::query()->firstOrFail();
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->put(route('admin.private-tours.update'), $this->payload($page, ['hero_image_file' => UploadedFile::fake()->image('banner.jpg')]))
            ->assertSessionHasNoErrors();

        $uploaded = $page->fresh()->hero_image;
        Storage::disk('public')->assertExists($uploaded);
        $this->get(route('tour-package-private'))->assertSee(Storage::disk('public')->url($uploaded));

        $this->put(route('admin.private-tours.update'), $this->payload($page->fresh(), ['hero_image' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNull($page->fresh()->hero_image);
        Storage::disk('public')->assertMissing($uploaded);
        $this->get(route('tour-package-private'))->assertSee('src="'.e(PrivateToursPage::DEFAULT_HERO_IMAGE).'"', false);
    }

    public function test_destination_links_and_images_must_be_safe(): void
    {
        $page = PrivateToursPage::query()->firstOrFail();

        $this->actingAs($this->createAdmin())
            ->put(route('admin.private-tours.update'), $this->payload($page, [
                'destinations' => [['name' => 'Bad', 'tagline' => '', 'link_url' => 'javascript:alert(1)', 'link_label' => '', 'image' => '../../.env', 'alt' => 'x']],
            ]))
            ->assertSessionHasErrors(['destinations.0.link_url', 'destinations.0.image']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(PrivateToursPage $page, array $overrides = []): array
    {
        return [...array_map(static fn (mixed $value): mixed => $value ?? '', $page->only((new PrivateToursPage)->getFillable())), ...$overrides];
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
