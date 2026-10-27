<?php

namespace Tests\Feature;

use App\Models\TicketingPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticketing_page_shows_airlines_promotions_and_the_enquiry_form(): void
    {
        $this->get(route('home'))->assertSee('href="'.route('ticketing').'"', false);

        $this->get(route('ticketing'))
            ->assertOk()
            ->assertSee('<title>Flight Tickets | Smart Holidays by Roystar Travel &amp; Tours</title>', false)
            ->assertSee('src="'.asset('images/ticketing/banner.png').'"', false)
            ->assertSeeInOrder(['alt="Batik Air"', 'alt="Singapore Airlines"', 'alt="Emirates"', 'alt="EVA Air"'], false)
            ->assertSeeInOrder(['Chase the Horizon', 'RM189', 'Explore Indonesia', 'RM109', 'Explore Thailand', 'RM139'])
            ->assertSee('Krabi · Hat Yai · Phuket · Bangkok')
            ->assertSee('href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20the%20Batik%20Air%20Explore%20Thailand%20fare%20%28RM139%29."', false)
            ->assertSee('Tickets Enquiry')
            ->assertSee('data-ticket-enquiry data-whatsapp="60165500590"', false)
            ->assertSee('<option>Qatar Airways</option>', false);
    }

    public function test_guests_cannot_open_the_editor(): void
    {
        $this->get(route('admin.ticketing.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.ticketing.update'), [])->assertRedirect(route('login'));
    }

    public function test_admin_can_update_airlines_and_promotions(): void
    {
        Storage::fake('public');
        $page = TicketingPage::query()->firstOrFail();

        $this->actingAs($this->createAdmin())
            ->get(route('admin.ticketing.edit'))
            ->assertOk()
            ->assertSee('<h1>Ticketing page</h1>', false);

        $this->get(route('admin.dashboard'))->assertSee('href="'.route('admin.ticketing.edit').'"', false);

        $this->put(route('admin.ticketing.update'), $this->payload($page, [
            'hero_title' => 'Fly anywhere',
            'airlines' => [
                ['name' => 'Malaysia Airlines', 'logo' => '', 'logo_file' => UploadedFile::fake()->image('mas.png')],
                ['name' => '', 'logo' => ''],
            ],
            'promotions' => [$page->promotions[2]],
        ]))->assertRedirect(route('admin.ticketing.edit'))
            ->assertSessionHas('status', 'Ticketing page updated.');

        $page->refresh();
        $this->assertSame('Malaysia Airlines', $page->airlines[0]['name']);
        Storage::disk('public')->assertExists($page->airlines[0]['logo']);
        $this->assertCount(1, $page->promotions);

        $this->get(route('ticketing'))
            ->assertSee('Fly anywhere')
            ->assertSee('alt="Malaysia Airlines"', false)
            ->assertSee('Explore Thailand')
            ->assertDontSee('Chase the Horizon')
            ->assertDontSee('alt="Batik Air"', false);
    }

    public function test_cleared_banner_falls_back_to_the_default_and_bad_values_are_rejected(): void
    {
        $page = TicketingPage::query()->firstOrFail();
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->put(route('admin.ticketing.update'), $this->payload($page, ['hero_image' => '']))
            ->assertSessionHasNoErrors();
        $this->assertNull($page->fresh()->hero_image);
        $this->get(route('ticketing'))->assertSee('src="'.asset(TicketingPage::DEFAULT_HERO_IMAGE).'"', false);

        $this->put(route('admin.ticketing.update'), $this->payload($page, [
            'whatsapp_number' => '+60 16-550',
            'promotions' => [['title' => 'Bad', 'airline' => 'X', 'image' => '../../.env', 'alt' => 'x']],
        ]))->assertSessionHasErrors(['whatsapp_number', 'promotions.0.image']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(TicketingPage $page, array $overrides = []): array
    {
        return [...array_map(static fn (mixed $value): mixed => $value ?? '', $page->only((new TicketingPage)->getFillable())), ...$overrides];
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
