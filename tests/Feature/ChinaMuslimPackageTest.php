<?php

namespace Tests\Feature;

use App\Models\ChinaAboutPage;
use App\Models\ChinaMuslimPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChinaMuslimPackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_clients_see_cards_but_no_new_button(): void
    {
        ChinaMuslimPackage::factory()->create(['title' => 'KUNMING DALI MUSLIM TOURS', 'price' => 4990, 'is_hot' => true]);

        $this->get(route('china-muslim'))
            ->assertOk()
            ->assertSee('KUNMING DALI MUSLIM TOURS')
            ->assertSee('RM4,990.00')
            ->assertSee('8D 7N')
            ->assertSee('HOT')
            ->assertDontSee('china-card-new', false);

        $this->actingAs(User::factory()->create())
            ->get(route('china-muslim'))
            ->assertDontSee('china-card-new', false)
            ->assertDontSee('china-card-edit', false);
    }

    public function test_card_with_details_renders_a_popup_with_itinerary_and_info_slides(): void
    {
        ChinaMuslimPackage::factory()->create([
            'details' => "DAY 01: KUALA LUMPUR ✈ SHANGHAI (MOB,D)\nMeet & Greet with tour guide\nDAY 02: SHANGHAI DISNEYLAND (B)\nTransfer to Disneyland\nNote: Subject to change.\nIncluded:\nReturn flight ticket\nExcluded:\nOwn Travel Insurance\nLanguage:\nMalay",
        ]);

        $this->get(route('china-muslim'))
            ->assertOk()
            ->assertSee('data-china-open', false)
            ->assertSee('DAY 02')
            ->assertSee('SHANGHAI DISNEYLAND')
            ->assertSee('MOB,D')
            ->assertSee('Return flight ticket')
            ->assertSee('Own Travel Insurance')
            ->assertSee('Subject to change.');
    }

    public function test_card_without_details_has_no_popup(): void
    {
        ChinaMuslimPackage::factory()->create();

        $this->get(route('china-muslim'))
            ->assertOk()
            ->assertDontSee('data-china-open', false);
    }

    public function test_admin_edit_form_shows_existing_slides_and_saves_added_ones(): void
    {
        Storage::fake('public');
        $package = ChinaMuslimPackage::factory()->create([
            'details' => "DAY 01: KUALA LUMPUR (D)\nGather at KLIA\nIncluded:\nReturn flight ticket",
        ]);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->get(route('admin.china-muslim.edit', $package))
            ->assertOk()
            ->assertSee('KUALA LUMPUR')
            ->assertSee('Gather at KLIA')
            ->assertSee('Return flight ticket')
            ->assertSee('data-china-add-slide', false);

        $this->actingAs($admin)
            ->put(route('admin.china-muslim.update', $package), [
                'title' => $package->title,
                'price' => 1000,
                'days' => 2,
                'nights' => 1,
                'slides' => [
                    ['title' => 'KUALA LUMPUR', 'meals' => 'D', 'items' => "Gather at KLIA\nFly out"],
                    ['title' => 'SHANGHAI', 'meals' => 'B,L', 'items' => 'City tour'],
                    ['title' => '', 'meals' => '', 'items' => ''],
                ],
                'sections' => ['included' => "Return flight ticket\nHotel", 'language' => 'Malay'],
                'note' => 'Subject to change.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(
            "DAY 01: KUALA LUMPUR (D)\nGather at KLIA\nFly out\nDAY 02: SHANGHAI (B,L)\nCity tour\nNote: Subject to change.\nIncluded:\nReturn flight ticket\nHotel\nLanguage:\nMalay",
            $package->refresh()->details,
        );
    }

    public function test_admin_sees_the_new_button_that_opens_the_admin_create_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        ChinaMuslimPackage::factory()->create();

        $this->actingAs($admin)->get(route('china-muslim'))
            ->assertOk()
            ->assertSee('class="china-card-new" href="'.route('admin.china-muslim.create').'"', false)
            ->assertSee('china-card-edit', false);

        $this->actingAs($admin)->get(route('admin.china-muslim.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertSee(route('admin.china-muslim'));
    }

    public function test_admin_pages_reject_guests_and_clients(): void
    {
        $package = ChinaMuslimPackage::factory()->create();

        $this->get(route('admin.china-muslim'))->assertRedirect();
        $this->actingAs(User::factory()->create())
            ->post(route('admin.china-muslim.store'), [])
            ->assertForbidden();
        $this->delete(route('admin.china-muslim.destroy', $package))->assertForbidden();
        $this->assertModelExists($package);
    }

    public function test_admin_can_create_edit_and_delete_a_card(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.china-muslim.store'), [
            'title' => '7D5N GUANGZHOU GUILIN MUSLIM TOURS',
            'price' => 2990,
            'days' => 7,
            'nights' => 5,
            'is_hot' => '1',
            'image' => UploadedFile::fake()->image('guilin.jpg'),
        ])->assertRedirect(route('admin.china-muslim'));

        $package = ChinaMuslimPackage::query()->firstOrFail();
        $this->assertTrue($package->is_hot);
        Storage::disk('public')->assertExists($package->image_path);
        $oldImage = $package->image_path;

        $this->actingAs($admin)->put(route('admin.china-muslim.update', $package), [
            'title' => 'UPDATED TITLE',
            'price' => 3100,
            'days' => 8,
            'nights' => 6,
            'image' => UploadedFile::fake()->image('new.png'),
        ])->assertRedirect(route('admin.china-muslim'));

        $package->refresh();
        $this->assertSame('UPDATED TITLE', $package->title);
        $this->assertFalse($package->is_hot);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($package->image_path);

        $this->actingAs($admin)->delete(route('admin.china-muslim.destroy', $package))
            ->assertRedirect(route('admin.china-muslim'));
        $this->assertModelMissing($package);
        Storage::disk('public')->assertMissing($package->image_path);
    }

    public function test_card_requires_valid_details_and_an_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->from(route('admin.china-muslim.create'))
            ->post(route('admin.china-muslim.store'), [
                'title' => '',
                'price' => -1,
                'days' => 0,
                'nights' => 'x',
                'link' => 'javascript:alert(1)',
                'image' => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['title', 'price', 'days', 'nights', 'link', 'image']);

        $this->assertDatabaseCount('china_muslim_packages', 0);
    }

    public function test_admin_can_edit_the_about_this_place_popup_and_gallery(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $about = ChinaAboutPage::query()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.china-muslim.create'))
            ->assertOk()
            ->assertSee('About this place popup');

        $payload = [
            'badge' => 'TRAVEL GUIDE',
            'title' => 'Discover Shanghai',
            'intro' => 'A modern city on the Huangpu River.',
            'facts' => $about->facts,
            'body' => $about->body,
            'note' => $about->note,
            'new_photos' => [UploadedFile::fake()->image('bund.jpg')],
        ];

        $this->actingAs($admin)
            ->from(route('admin.china-muslim.create'))->put(route('admin.china-muslim.about'), $payload)
            ->assertRedirect(route('admin.china-muslim.create'));

        $about->refresh();
        $this->assertCount(1, $about->gallery);
        Storage::disk('public')->assertExists($about->gallery[0]);

        $this->get(route('china-muslim'))
            ->assertSee('Discover Shanghai')
            ->assertSee('A modern city on the Huangpu River.')
            ->assertSee($about->gallery[0]);

        $photo = $about->gallery[0];

        $this->actingAs($admin)
            ->from(route('admin.china-muslim.create'))->put(route('admin.china-muslim.about'), [...$payload, 'new_photos' => [], 'remove_photos' => [$photo]])
            ->assertRedirect(route('admin.china-muslim.create'));

        $this->assertSame([], $about->fresh()->gallery);
        Storage::disk('public')->assertMissing($photo);
    }

    public function test_non_admins_cannot_edit_the_about_popup(): void
    {
        $this->put(route('admin.china-muslim.about'), [])->assertRedirect();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.china-muslim.about'), [])
            ->assertForbidden();
    }
}
