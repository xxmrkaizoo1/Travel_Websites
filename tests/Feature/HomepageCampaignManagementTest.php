<?php

namespace Tests\Feature;

use App\Models\HomepageCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageCampaignManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_homepage_campaign_management(): void
    {
        $this->get(route('admin.homepage'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.homepage.create'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.homepage.edit', 'season-of-discovery'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_forbidden_from_editing_homepage_campaigns(): void
    {
        $user = User::factory()->create();
        $campaign = HomepageCampaign::query()->where('slug', 'season-of-discovery')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.homepage'))
            ->assertForbidden();

        $this->put(route('admin.homepage.update', $campaign), $this->campaignData([
            'headline' => 'Unauthorized update',
        ]))->assertForbidden();

        $this->post(route('admin.homepage.store'), $this->campaignData())
            ->assertForbidden();

        $this->delete(route('admin.homepage.destroy', $campaign))
            ->assertForbidden();

        $this->assertDatabaseHas('homepage_campaigns', [
            'id' => $campaign->id,
            'headline' => 'See you',
        ]);
    }

    public function test_admin_can_view_homepage_campaigns_and_edit_a_campaign(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.homepage'))
            ->assertOk()
            ->assertSee('Homepage content')
            ->assertSee('A new season of discovery')
            ->assertSee('Travel with peace of mind')
            ->assertSee('Add campaign');

        $this->get(route('admin.homepage.create'))
            ->assertOk()
            ->assertSee('Create campaign')
            ->assertSee('name="design_key"', false)
            ->assertSee('name="image_main"', false);

        $this->get(route('admin.homepage.edit', 'season-of-discovery'))
            ->assertOk()
            ->assertSee('Campaign text')
            ->assertSee('name="image_main"', false);
    }

    public function test_admin_can_create_and_delete_homepage_campaigns(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $payload = $this->campaignData([
            'kicker' => 'Island escapes',
            'headline' => 'Find your',
            'headline_highlight' => 'next escape.',
            'design_key' => 'group',
            'image_main' => UploadedFile::fake()->image('main.jpg', 1200, 800),
            'image_top' => UploadedFile::fake()->image('top.png', 600, 400),
            'image_bottom' => UploadedFile::fake()->image('bottom.webp', 600, 400),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.homepage.store'), $payload)
            ->assertRedirect(route('admin.homepage'))
            ->assertSessionHas('status', 'Homepage campaign added successfully.');

        $campaign = HomepageCampaign::query()->where('kicker', 'Island escapes')->firstOrFail();
        $this->assertSame('island-escapes-find-your', $campaign->slug);
        $this->assertSame('group', $campaign->design_key);
        $this->assertSame(4, $campaign->position);
        Storage::disk('public')->assertExists($campaign->image_main);
        Storage::disk('public')->assertExists($campaign->image_top);
        Storage::disk('public')->assertExists($campaign->image_bottom);

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee('Island escapes')
            ->assertSee('Find your');

        $this->delete(route('admin.homepage.destroy', $campaign))
            ->assertRedirect(route('admin.homepage'))
            ->assertSessionHas('status', 'Homepage campaign deleted successfully.');

        $this->assertDatabaseMissing('homepage_campaigns', ['id' => $campaign->id]);
        Storage::disk('public')->assertMissing($campaign->image_main);
        Storage::disk('public')->assertMissing($campaign->image_top);
        Storage::disk('public')->assertMissing($campaign->image_bottom);
    }

    public function test_admin_can_create_a_normal_banner_campaign_with_one_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $payload = $this->campaignData([
            'kicker' => 'Simple banner',
            'layout' => 'banner',
            'image_main' => UploadedFile::fake()->image('main.jpg', 1200, 800),
        ]);
        unset($payload['sticker'], $payload['ribbon'], $payload['brand_top'], $payload['brand_bottom']);

        $this->actingAs($admin)
            ->post(route('admin.homepage.store'), $payload)
            ->assertRedirect(route('admin.homepage'));

        $campaign = HomepageCampaign::query()->where('kicker', 'Simple banner')->firstOrFail();
        $this->assertTrue($campaign->isBanner());

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee('campaign-slide-banner', false)
            ->assertSee('Simple banner');

        $this->delete(route('admin.homepage.destroy', $campaign))
            ->assertRedirect(route('admin.homepage'));
    }

    public function test_collage_campaign_still_requires_collage_images(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.homepage.store'), $this->campaignData([
                'layout' => 'collage',
                'image_main' => UploadedFile::fake()->image('main.jpg'),
            ]))
            ->assertSessionHasErrors(['image_top', 'image_bottom']);
    }

    public function test_admin_cannot_delete_the_last_homepage_campaign(): void
    {
        $admin = $this->createAdmin();
        HomepageCampaign::query()->where('slug', '!=', 'season-of-discovery')->delete();
        $campaign = HomepageCampaign::query()->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.homepage.destroy', $campaign))
            ->assertRedirect(route('admin.homepage'))
            ->assertSessionHasErrors('campaign');

        $this->assertDatabaseHas('homepage_campaigns', [
            'id' => $campaign->id,
            'slug' => 'season-of-discovery',
        ]);
    }

    public function test_admin_can_update_campaign_copy_and_images_on_homepage(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $campaign = HomepageCampaign::query()->where('slug', 'season-of-discovery')->firstOrFail();
        $mainImage = UploadedFile::fake()->image('new-main.jpg', 1200, 800);
        $topImage = UploadedFile::fake()->image('new-top.png', 600, 400);
        $bottomImage = UploadedFile::fake()->image('new-bottom.webp', 600, 400);

        $this->actingAs($admin)
            ->put(route('admin.homepage.update', $campaign), $this->campaignData([
                'headline' => '<script>alert(1)</script>',
                'image_main' => $mainImage,
                'image_top' => $topImage,
                'image_bottom' => $bottomImage,
                'design_key' => 'group',
                'position' => 99,
            ]))
            ->assertRedirect(route('admin.homepage.edit', $campaign))
            ->assertSessionHas('status', 'Homepage campaign updated successfully.');

        $campaign->refresh();

        $this->assertDatabaseHas('homepage_campaigns', [
            'id' => $campaign->id,
            'headline' => '<script>alert(1)</script>',
            'design_key' => 'group',
            'position' => 1,
        ]);
        Storage::disk('public')->assertExists($campaign->image_main);
        Storage::disk('public')->assertExists($campaign->image_top);
        Storage::disk('public')->assertExists($campaign->image_bottom);

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('homepage-campaigns/', false);
    }

    public function test_invalid_campaign_image_is_rejected_without_updating_content(): void
    {
        $admin = $this->createAdmin();
        $campaign = HomepageCampaign::query()->where('slug', 'season-of-discovery')->firstOrFail();
        $invalidImage = UploadedFile::fake()->create('not-an-image.gif', 10, 'image/gif');

        $this->actingAs($admin)
            ->from(route('admin.homepage.edit', $campaign))
            ->put(route('admin.homepage.update', $campaign), $this->campaignData([
                'headline' => 'Should not be saved',
                'image_main' => $invalidImage,
            ]))
            ->assertRedirect(route('admin.homepage.edit', $campaign))
            ->assertSessionHasErrors('image_main');

        $this->assertDatabaseHas('homepage_campaigns', [
            'id' => $campaign->id,
            'headline' => 'See you',
        ]);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    private function campaignData(array $overrides = []): array
    {
        return array_merge([
            'kicker' => 'Updated campaign introduction',
            'headline' => 'A new journey',
            'headline_highlight' => 'starts here.',
            'design_key' => 'fair',
            'body' => "First line\nSecond line\nThird line",
            'cta_label' => 'PLAN A JOURNEY',
            'image_alt' => 'A beautiful destination',
            'sticker' => "TRAVEL\nTOGETHER",
            'ribbon' => 'SMART HOLIDAYS',
            'brand_top' => 'SMART',
            'brand_bottom' => 'MUSLIM',
        ], $overrides);
    }
}
