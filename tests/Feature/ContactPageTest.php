<?php

namespace Tests\Feature;

use App\Models\ContactPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_lists_the_offices_and_map(): void
    {
        $this->get(route('home'))->assertSee('href="'.route('contact').'"', false);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('<title>Contact Us | Smart Holidays by Roystar Travel &amp; Tours</title>', false)
            ->assertSeeInOrder(['ROYSTAR TRAVEL &amp; TOURS SDN BHD', 'HONG THAI TOURS &amp; TRAVEL (M) SDN BHD', 'B.T.T. TRAVEL SERVICES SDN BHD'], false)
            ->assertSee('src="'.asset('images/contact/btt.png').'"', false)
            ->assertSee('href="tel:+60321430188">03-2143 0188</a>', false)
            ->assertSee('href="tel:+60321430288">2143 0288</a>', false)
            ->assertSee('href="tel:+60321411276">2141 1276</a>', false)
            ->assertSee('href="https://wa.me/60162277691"', false)
            ->assertSee('href="mailto:inbound4@btttravel.com.my"', false)
            ->assertSee('href="https://www.smartholidays.com.my"', false)
            ->assertDontSee('Send a message')
            ->assertSee('src="https://www.google.com/maps?output=embed&amp;q=Roystar%20Travel%20%26%20Tours', false);
    }

    public function test_guests_cannot_open_the_editor(): void
    {
        $this->get(route('admin.contact.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.contact.update'), [])->assertRedirect(route('login'));
    }

    public function test_admin_can_update_the_companies(): void
    {
        Storage::fake('public');
        $page = ContactPage::query()->firstOrFail();

        $this->actingAs($this->createAdmin())
            ->get(route('admin.contact.edit'))
            ->assertOk()
            ->assertSee('<h1>Contact Us page</h1>', false);

        $this->get(route('admin.dashboard'))->assertSee('href="'.route('admin.contact.edit').'"', false);

        $this->put(route('admin.contact.update'), $this->payload($page, [
            'companies' => [
                ['name' => 'Smart Holidays Penang', 'address' => 'Georgetown, Penang', 'phone' => '04-222 3333', 'whatsapp' => '', 'email' => '', 'website' => '', 'logo' => '', 'logo_file' => UploadedFile::fake()->image('logo.png')],
                ['name' => '', 'address' => '', 'phone' => '', 'logo' => ''],
            ],
            'map_query' => 'Georgetown, Penang',
        ]))->assertRedirect(route('admin.contact.edit'))
            ->assertSessionHas('status', 'Contact page updated.');

        $page->refresh();
        $this->assertCount(1, $page->companies);
        Storage::disk('public')->assertExists($page->companies[0]['logo']);

        $this->get(route('contact'))
            ->assertSee('Smart Holidays Penang')
            ->assertSee('href="tel:+6042223333"', false)
            ->assertSee('q=Georgetown%2C%20Penang', false)
            ->assertDontSee('B.T.T. TRAVEL SERVICES');
    }

    public function test_bad_values_are_rejected(): void
    {
        $page = ContactPage::query()->firstOrFail();

        $this->actingAs($this->createAdmin())
            ->put(route('admin.contact.update'), $this->payload($page, [
                'companies' => [['name' => 'X', 'address' => 'Y', 'phone' => 'call me', 'website' => 'javascript:alert(1)', 'logo' => '../../.env']],
            ]))->assertSessionHasErrors(['companies.0.phone', 'companies.0.website', 'companies.0.logo']);

        $this->put(route('admin.contact.update'), $this->payload($page, ['companies' => []]))
            ->assertSessionHasErrors('companies');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(ContactPage $page, array $overrides = []): array
    {
        return [...array_map(static fn (mixed $value): mixed => $value ?? '', $page->only((new ContactPage)->getFillable())), ...$overrides];
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
