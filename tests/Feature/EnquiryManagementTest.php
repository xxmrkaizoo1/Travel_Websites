<?php

namespace Tests\Feature;

use App\Enums\EnquirySource;
use App\Enums\EnquiryStatus;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnquiryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_enquiry_form_sends_enquiries_to_the_inbox(): void
    {
        $this->get(route('ticketing'))
            ->assertSee('data-enquiry-url="'.route('enquiries.store').'"', false)
            ->assertSee('name="_token"', false);

        $this->postJson(route('enquiries.store'), [
            'name' => 'Hafiz',
            'phone' => '0123456789',
            'destination' => 'Flight ticket: Kuala Lumpur → Bangkok',
            'message' => "Trip: Return\nPassengers: 2",
        ])->assertCreated();

        $enquiry = Enquiry::query()->sole();
        $this->assertSame(EnquirySource::Website, $enquiry->source);
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertNull($enquiry->user_id);
    }

    public function test_signed_in_client_enquiries_are_linked_to_their_account(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)
            ->postJson(route('enquiries.store'), ['name' => $client->name, 'phone' => '0123456789', 'message' => 'Any seats to Seoul?'])
            ->assertCreated();

        $this->assertTrue(Enquiry::query()->sole()->client->is($client));
    }

    public function test_website_enquiries_are_validated_and_rate_limited(): void
    {
        $this->postJson(route('enquiries.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'message']);

        $payload = ['name' => 'Spammer', 'phone' => '0123456789', 'message' => 'Hello'];
        foreach (range(1, 4) as $attempt) {
            $this->postJson(route('enquiries.store'), $payload)->assertCreated();
        }
        $this->postJson(route('enquiries.store'), $payload)->assertTooManyRequests();

        $this->assertDatabaseCount('enquiries', 4);
    }

    public function test_admin_logs_an_enquiry_and_it_links_to_a_matching_client(): void
    {
        $client = User::factory()->create(['email' => 'aisyah@example.com']);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.enquiries.store'), [
                'name' => 'Aisyah',
                'email' => 'aisyah@example.com',
                'phone' => '',
                'destination' => 'Melbourne',
                'message' => 'Called about the March departure.',
                'source' => 'phone',
            ])
            ->assertRedirect(route('admin.enquiries.index'));

        $enquiry = Enquiry::query()->sole();
        $this->assertSame(EnquirySource::Phone, $enquiry->source);
        $this->assertTrue($enquiry->client->is($client));

        $this->post(route('admin.enquiries.store'), ['name' => 'No Contact', 'email' => '', 'phone' => '', 'message' => 'Hi', 'source' => 'walk_in'])
            ->assertSessionHasErrors(['email' => 'Add an email address or a phone number so the team can reply.']);
        $this->assertDatabaseCount('enquiries', 1);
    }

    public function test_inbox_tabs_status_changes_and_deletion(): void
    {
        $open = Enquiry::factory()->create(['name' => 'Open Olivia']);
        $closed = Enquiry::factory()->status(EnquiryStatus::Closed)->create(['name' => 'Closed Carl']);
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.enquiries.index'))
            ->assertOk()
            ->assertSee('Open Olivia')
            ->assertSee('href="'.route('admin.bookings.create', ['enquiry' => $open->id]).'"', false)
            ->assertDontSee('Closed Carl');
        $this->get(route('admin.enquiries.index', ['tab' => 'closed']))
            ->assertSee('Closed Carl')
            ->assertDontSee('Open Olivia');

        $this->patch(route('admin.enquiries.update', $open), ['status' => 'in_progress'])
            ->assertSessionHas('status', 'Enquiry from Open Olivia marked as In progress.');
        $this->assertSame(EnquiryStatus::InProgress, $open->fresh()->status);
        $this->patch(route('admin.enquiries.update', $open), ['status' => 'archived'])->assertSessionHasErrors('status');

        $this->delete(route('admin.enquiries.destroy', $closed));
        $this->assertModelMissing($closed);
    }

    public function test_pending_enquiries_show_on_the_sidebar_badge(): void
    {
        Enquiry::factory()->count(3)->create();
        Enquiry::factory()->status(EnquiryStatus::Closed)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.bookings.index'))
            ->assertSee('<span class="admin-nav-count" aria-label="3 pending">3</span>', false)
            ->assertSee('aria-label="3 pending enquiries"', false);
    }

    public function test_client_sees_their_bookings_in_the_profile_panel(): void
    {
        $client = User::factory()->create();
        $booking = Booking::factory()->for($client, 'client')->create(['destination' => 'Melbourne', 'status' => 'confirmed']);
        Booking::factory()->create(['destination' => 'Somebody Else Trip']);

        $this->actingAs($client)
            ->get(route('home'))
            ->assertSee('My bookings')
            ->assertSee($booking->fresh()->reference)
            ->assertSee('<span class="profile-booking-status is-confirmed">Confirmed</span>', false)
            ->assertDontSee('Somebody Else Trip');
    }
}
