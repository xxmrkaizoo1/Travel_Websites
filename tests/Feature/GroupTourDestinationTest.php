<?php

namespace Tests\Feature;

use App\Models\GroupTourDestination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GroupTourDestinationTest extends TestCase
{
    use RefreshDatabase;

    public function test_malaysia_page_lists_states_grouped_by_region(): void
    {
        $this->get(route('group-tours.destination', 'malaysia'))
            ->assertOk()
            ->assertSee('<title>Malaysia | Group Tours | Smart Holidays</title>', false)
            ->assertSee('<strong>16</strong> states to explore', false)
            ->assertSeeInOrder(['href="#region-northern"', 'href="#region-borneo"'], false)
            ->assertSeeInOrder(['id="region-northern"', 'Capital · George Town', 'Penang', 'id="region-central"', 'Federal Territory', 'Kuala Lumpur', 'id="region-borneo"', 'Sabah'], false)
            ->assertSee('id="state-negeri-sembilan"', false)
            ->assertSee('data-state-search', false)
            ->assertSeeInOrder(['<option value="">All regions</option>', '<option value="northern">Northern</option>', '<option value="borneo">Borneo</option>'], false)
            ->assertSee('data-region="northern" data-search="penang george town', false)
            ->assertSee('<strong>3</strong> states', false)
            ->assertSee('Sabah · Sarawak · Labuan')
            ->assertSee('href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20group%20tours%20to%20Penang%2C%20Malaysia."', false);
    }

    public function test_malaysia_card_and_menu_lead_to_the_destination_page(): void
    {
        $malaysiaUrl = route('group-tours.destination', 'malaysia');

        $this->get(route('group-tours'))
            ->assertOk()
            ->assertSee('href="/group-tours/malaysia"', false)
            ->assertSeeInOrder(['aria-controls="group-tour-destinations-menu"', 'href="'.$malaysiaUrl.'"', 'aria-controls="group-tour-malaysia-menu"', 'href="'.$malaysiaUrl.'#region-northern"', 'href="'.$malaysiaUrl.'#region-borneo"', 'Booking Conditions'], false);
    }

    public function test_unknown_destination_returns_not_found(): void
    {
        $this->get('/group-tours/atlantis')->assertNotFound();
    }

    public function test_dashboard_lists_destination_pages_under_group_tours(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Tour Destinations', 'href="'.route('admin.group-tour-destinations.edit', 'malaysia').'"', 'Malaysia', '16 states', 'New destination page', 'Booking Conditions'], false);
    }

    public function test_guest_and_non_admin_cannot_manage_destination_pages(): void
    {
        $this->get(route('admin.group-tour-destinations.edit', 'malaysia'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->put(route('admin.group-tour-destinations.update', 'malaysia'), [])
            ->assertForbidden();
        $this->post(route('admin.group-tour-destinations.store'), ['name' => 'Vietnam'])->assertForbidden();
    }

    public function test_admin_can_edit_states_with_photo_upload(): void
    {
        Storage::fake('public');
        $destination = GroupTourDestination::query()->where('slug', 'malaysia')->firstOrFail();
        $states = $destination->states;
        $states[2] = [...$states[2], 'tagline' => 'Street food capital.', 'image_file' => UploadedFile::fake()->image('penang.jpg', 1200, 800), 'alt' => 'George Town shophouses'];
        unset($states[15]);
        $states[] = ['name' => '', 'region' => 'northern', 'capital' => '', 'tagline' => '', 'image' => '', 'alt' => ''];

        $this->actingAs($this->createAdmin())
            ->get(route('admin.group-tour-destinations.edit', $destination))
            ->assertOk()
            ->assertSee('data-gallery-paged="name"', false)
            ->assertSee('name="states[2][region]"', false);

        $this->put(route('admin.group-tour-destinations.update', $destination), $this->payload($destination, ['states' => $states]))
            ->assertRedirect(route('admin.group-tour-destinations.edit', $destination))
            ->assertSessionHas('status', 'Malaysia page updated.');

        $saved = $destination->fresh()->states;
        $this->assertCount(15, $saved);
        $this->assertSame('Street food capital.', $saved[2]['tagline']);
        Storage::disk('public')->assertExists($saved[2]['image']);

        $this->get(route('group-tours.destination', $destination))
            ->assertSee('alt="George Town shophouses"', false)
            ->assertDontSee('Labuan');
    }

    public function test_admin_can_replace_a_region_slide_photo(): void
    {
        Storage::fake('public');
        $destination = GroupTourDestination::query()->where('slug', 'malaysia')->firstOrFail();
        $regionImages = $destination->region_images;
        $regionImages['borneo'] = ['image' => '', 'alt' => 'Mount Kinabalu', 'image_file' => UploadedFile::fake()->image('kinabalu.jpg', 1200, 800)];
        $regionImages['central'] = ['image' => '', 'alt' => ''];

        $this->actingAs($this->createAdmin())
            ->get(route('admin.group-tour-destinations.edit', $destination))
            ->assertSee('name="region_images[borneo][image]"', false);

        $this->put(route('admin.group-tour-destinations.update', $destination), $this->payload($destination, ['region_images' => $regionImages]))
            ->assertRedirect(route('admin.group-tour-destinations.edit', $destination));

        $saved = $destination->fresh()->region_images;
        Storage::disk('public')->assertExists($saved['borneo']['image']);
        $this->assertSame('', $saved['central']['image']);

        $this->get(route('group-tours.destination', $destination))
            ->assertSee('<img src="'.Storage::disk('public')->url($saved['borneo']['image']).'" alt="Mount Kinabalu"', false)
            ->assertSee('<img src="'.e($destination->hero_image).'" alt=""', false);
    }

    public function test_admin_sees_add_edit_and_remove_buttons_on_the_page(): void
    {
        $malaysiaStatesUrl = '/admin/group-tours/destinations/malaysia/states';

        $this->get(route('group-tours.destination', 'malaysia'))
            ->assertOk()
            ->assertDontSee($malaysiaStatesUrl, false);

        $this->actingAs($this->createAdmin())
            ->get(route('group-tours.destination', 'malaysia'))
            ->assertSee('href="'.route('admin.group-tour-destinations.states.create', ['malaysia', 'region' => 'borneo']).'"', false)
            ->assertSee('href="'.route('admin.group-tour-destinations.states.edit', ['malaysia', 'kuala-lumpur']).'"', false)
            ->assertSee('action="'.route('admin.group-tour-destinations.states.destroy', ['malaysia', 'kuala-lumpur']).'" data-confirm="Remove Kuala Lumpur from this page?"', false);
    }

    public function test_admin_can_add_a_state_from_the_page(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.group-tour-destinations.states.create', ['malaysia', 'region' => 'borneo']))
            ->assertOk()
            ->assertSee('<option value="borneo" selected>Borneo</option>', false);

        $this->post(route('admin.group-tour-destinations.states.store', 'malaysia'), [
            'name' => 'Tioman Island',
            'region' => 'east-coast',
            'capital' => '',
            'tagline' => 'Coral reefs and jungle trails.',
        ])->assertRedirect(route('group-tours.destination', 'malaysia').'#state-tioman-island')
            ->assertSessionHas('status', 'Tioman Island added.');

        $states = GroupTourDestination::query()->where('slug', 'malaysia')->firstOrFail()->states;
        $this->assertCount(17, $states);
        $this->assertSame(['name' => 'Tioman Island', 'region' => 'east-coast', 'capital' => '', 'tagline' => 'Coral reefs and jungle trails.', 'image' => '', 'alt' => ''], $states[16]);

        $this->get(route('group-tours.destination', 'malaysia'))
            ->assertSee('Tioman Island added.')
            ->assertSeeInOrder(['id="region-east-coast"', 'Kelantan', 'Tioman Island', 'id="region-borneo"'], false);

        $this->post(route('admin.group-tour-destinations.states.store', 'malaysia'), ['name' => 'penang', 'region' => 'northern'])
            ->assertSessionHasErrors(['name' => 'Another state on this page already has that name.']);
    }

    public function test_admin_can_edit_a_state_photo_and_the_old_upload_is_removed(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $editUrl = route('admin.group-tour-destinations.states.update', ['malaysia', 'penang']);

        $this->actingAs($admin)
            ->get(route('admin.group-tour-destinations.states.edit', ['malaysia', 'penang']))
            ->assertOk()
            ->assertSee('value="George Town"', false);

        $this->put($editUrl, ['name' => 'Penang', 'region' => 'northern', 'capital' => 'George Town', 'image_file' => UploadedFile::fake()->image('a.jpg')])
            ->assertRedirect(route('group-tours.destination', 'malaysia').'#state-penang');
        $firstPhoto = GroupTourDestination::query()->where('slug', 'malaysia')->firstOrFail()->states[2]['image'];
        Storage::disk('public')->assertExists($firstPhoto);

        $this->put($editUrl, ['name' => 'Pulau Pinang', 'region' => 'northern', 'capital' => 'George Town', 'image_file' => UploadedFile::fake()->image('b.jpg')]);

        $state = GroupTourDestination::query()->where('slug', 'malaysia')->firstOrFail()->states[2];
        $this->assertSame('Pulau Pinang', $state['name']);
        Storage::disk('public')->assertExists($state['image']);
        Storage::disk('public')->assertMissing($firstPhoto);
        $this->get(route('admin.group-tour-destinations.states.edit', ['malaysia', 'penang']))->assertNotFound();

        $this->get(route('admin.group-tour-destinations.states.edit', ['malaysia', 'pulau-pinang']))
            ->assertSee('data-image-clear', false);

        $this->put(route('admin.group-tour-destinations.states.update', ['malaysia', 'pulau-pinang']), ['name' => 'Pulau Pinang', 'region' => 'northern', 'image' => '']);

        $this->assertSame('', GroupTourDestination::query()->where('slug', 'malaysia')->firstOrFail()->states[2]['image']);
        Storage::disk('public')->assertMissing($state['image']);
        $this->get(route('group-tours.destination', 'malaysia'))
            ->assertSee('<article class="destination-state region-northern region-tone-0" id="state-pulau-pinang"', false);
    }

    public function test_admin_can_remove_a_state(): void
    {
        $this->actingAs($this->createAdmin())
            ->delete(route('admin.group-tour-destinations.states.destroy', ['malaysia', 'labuan']))
            ->assertRedirect(route('group-tours.destination', 'malaysia').'#region-borneo')
            ->assertSessionHas('status', 'Labuan removed.');

        $this->assertNotContains('Labuan', array_column(GroupTourDestination::query()->where('slug', 'malaysia')->firstOrFail()->states, 'name'));
        $this->delete(route('admin.group-tour-destinations.states.destroy', ['malaysia', 'labuan']))->assertNotFound();
    }

    public function test_guest_and_non_admin_cannot_change_single_states(): void
    {
        $this->post(route('admin.group-tour-destinations.states.store', 'malaysia'), ['name' => 'X', 'region' => 'northern'])
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.group-tour-destinations.states.destroy', ['malaysia', 'penang']))
            ->assertForbidden();
        $this->put(route('admin.group-tour-destinations.states.update', ['malaysia', 'penang']), ['name' => 'X', 'region' => 'northern'])
            ->assertForbidden();
    }

    public function test_vietnam_thailand_and_indonesia_have_their_own_pages_and_regions(): void
    {
        $this->get(route('group-tours'))
            ->assertSee('href="/group-tours/vietnam"', false)
            ->assertSee('href="/group-tours/thailand"', false)
            ->assertSee('href="/group-tours/indonesia"', false)
            ->assertSeeInOrder(['aria-controls="group-tour-thailand-menu"', 'href="'.route('group-tours.destination', 'thailand').'#region-northeast"', 'Northeast (Isan)'], false);

        $this->get(route('group-tours.destination', 'vietnam'))
            ->assertOk()
            ->assertSee('<title>Vietnam | Group Tours | Smart Holidays</title>', false)
            ->assertSee('<strong>10</strong> destinations to explore', false)
            ->assertSee('placeholder="Search destinations or cities…"', false)
            ->assertSeeInOrder(['id="region-northern"', 'Ha Long Bay', 'id="region-central"', 'Hoi An', 'id="region-southern"', 'Mekong Delta'], false)
            ->assertSee('<span class="destination-state__capital">Quang Ninh</span>', false)
            ->assertDontSee('Capital · ');

        $this->get(route('group-tours.destination', 'indonesia'))
            ->assertOk()
            ->assertSeeInOrder(['<option value="java">Java</option>', '<option value="bali-nusa-tenggara">Bali &amp; Nusa Tenggara</option>', '<option value="sumatra">Sumatra</option>'], false)
            ->assertSee('Banda Aceh');
    }

    public function test_admin_can_rename_add_and_remove_regions(): void
    {
        $destination = GroupTourDestination::query()->where('slug', 'vietnam')->firstOrFail();
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.group-tour-destinations.edit', $destination))
            ->assertSee('name="regions[central]" value="Central"', false)
            ->assertSee('name="new_regions[]"', false);

        $this->put(route('admin.group-tour-destinations.update', $destination), $this->payload($destination, [
            'regions' => ['northern' => 'The North', 'central' => 'Central', 'southern' => ''],
            'new_regions' => ['Highlands', ''],
        ]))->assertSessionHasErrors(['states.7.region' => 'Each place must use one of the regions on this page. Move places out of a region before removing it.']);

        $states = array_map(static fn (array $state): array => $state['region'] === 'southern' ? [...$state, 'region' => 'central'] : $state, $destination->states);

        $this->put(route('admin.group-tour-destinations.update', $destination), $this->payload($destination, [
            'states' => $states,
            'regions' => ['northern' => 'The North', 'central' => 'Central', 'southern' => ''],
            'new_regions' => ['Highlands', ''],
            'place_term' => 'Province',
        ]))->assertRedirect(route('admin.group-tour-destinations.edit', $destination));

        $destination->refresh();
        $this->assertSame(['northern' => 'The North', 'central' => 'Central', 'highlands' => 'Highlands'], $destination->regionOptions());
        $this->assertSame('province', $destination->place_term);

        $this->get(route('group-tours.destination', $destination))
            ->assertSee('<strong>10</strong> provinces to explore', false)
            ->assertSee('<option value="northern">The North</option>', false)
            ->assertDontSee('id="region-southern"', false);

        $this->post(route('admin.group-tour-destinations.states.store', $destination), ['name' => 'Da Lat', 'region' => 'highlands'])
            ->assertSessionHasNoErrors();
        $this->post(route('admin.group-tour-destinations.states.store', $destination), ['name' => 'Can Tho', 'region' => 'southern'])
            ->assertSessionHasErrors(['region']);
    }

    public function test_state_needs_a_known_region_and_safe_image(): void
    {
        $destination = GroupTourDestination::query()->where('slug', 'malaysia')->firstOrFail();
        $states = $destination->states;
        $states[0]['region'] = 'atlantis';
        $states[1]['image'] = 'private/secret.jpg';

        $this->actingAs($this->createAdmin())
            ->put(route('admin.group-tour-destinations.update', $destination), $this->payload($destination, [
                'states' => $states,
                'region_images' => ['northern' => ['image' => 'javascript:alert(1)']],
            ]))
            ->assertSessionHasErrors(['states.0.region', 'states.1.image', 'region_images.northern.image']);
    }

    public function test_admin_can_create_a_destination_page_with_a_unique_address(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.group-tour-destinations.store'), ['name' => 'Viet Nam'])
            ->assertRedirect(route('admin.group-tour-destinations.edit', 'viet-nam'));

        $this->get(route('group-tours.destination', 'viet-nam'))
            ->assertOk()
            ->assertSee('Discover Viet Nam')
            ->assertSee('Destinations for Viet Nam are coming soon.');

        $this->post(route('admin.group-tour-destinations.store'), ['name' => 'Malaysia'])
            ->assertSessionHasErrors(['slug' => 'A destination page with that web address already exists.']);
        $this->post(route('admin.group-tour-destinations.store'), ['name' => 'Booking Conditions'])
            ->assertSessionHasErrors(['slug' => 'That web address is already used by another Group Tours page.']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(GroupTourDestination $destination, array $overrides = []): array
    {
        return [...$destination->only(['name', 'meta_description', 'hero_eyebrow', 'heading', 'intro', 'states_heading', 'states']), ...$overrides];
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
