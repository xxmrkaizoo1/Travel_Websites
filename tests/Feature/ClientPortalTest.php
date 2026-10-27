<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\EnquirySource;
use App\Enums\EnquiryStatus;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_go_to_the_client_login_and_admins_to_their_dashboard(): void
    {
        $this->get(route('client.dashboard'))->assertRedirect(route('client.login'));
        $this->get(route('client.bookings.index'))->assertRedirect(route('client.login'));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('client.dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_overview_shows_the_next_trip_countdown_and_summary(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 09:00'));
        $client = User::factory()->create(['name' => 'Aisyah Rahman']);
        Booking::factory()->for($client, 'client')->status(BookingStatus::Confirmed)->create(['destination' => 'Melbourne', 'travel_date' => '2026-10-25', 'adults' => 2, 'children' => 1]);
        Booking::factory()->for($client, 'client')->status(BookingStatus::Pending)->create(['destination' => 'Seoul', 'travel_date' => '2026-12-01', 'adults' => 2]);
        Booking::factory()->for($client, 'client')->status(BookingStatus::Cancelled)->create(['destination' => 'Bali', 'travel_date' => '2026-10-20']);
        Booking::factory()->create(['destination' => 'Someone Else Trip', 'travel_date' => '2026-10-16']);
        Enquiry::factory()->for($client, 'client')->create(['destination' => 'Tokyo in spring']);

        $this->actingAs($client)
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Welcome back, Aisyah!')
            ->assertSeeInOrder(['YOUR NEXT TRIP', 'Melbourne', '10 days to go', 'More upcoming trips', 'Seoul'], false)
            ->assertViewHas('stats', ['upcoming' => 2, 'trips' => 2, 'openRequests' => 1, 'travellers' => 5])
            ->assertSee('Tokyo in spring')
            ->assertDontSee('Someone Else Trip');
    }

    public function test_bookings_are_grouped_into_upcoming_past_and_cancelled(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 09:00'));
        $client = User::factory()->create();
        Booking::factory()->for($client, 'client')->create(['destination' => 'Future Bali', 'travel_date' => '2026-11-01']);
        Booking::factory()->for($client, 'client')->status(BookingStatus::Completed)->create(['destination' => 'Past Seoul', 'travel_date' => '2026-03-01']);
        Booking::factory()->for($client, 'client')->status(BookingStatus::Cancelled)->create(['destination' => 'Cancelled Tokyo', 'travel_date' => '2026-12-01']);
        $this->actingAs($client);

        $destinationsOn = fn (?string $tab): array => $this->get(route('client.bookings.index', array_filter(['tab' => $tab])))
            ->assertOk()
            ->viewData('bookings')
            ->pluck('destination')
            ->all();

        $this->assertSame(['Future Bali'], $destinationsOn(null));
        $this->assertSame(['Past Seoul'], $destinationsOn('past'));
        $this->assertSame(['Cancelled Tokyo'], $destinationsOn('cancelled'));
        $this->assertSame(['Cancelled Tokyo', 'Future Bali', 'Past Seoul'], $destinationsOn('all'));
        $this->get(route('client.bookings.index', ['tab' => 'archived']))->assertSessionHasErrors('tab');
    }

    public function test_client_sees_their_booking_details_but_not_admin_notes_or_other_clients_bookings(): void
    {
        $client = User::factory()->create();
        $booking = Booking::factory()->for($client, 'client')->status(BookingStatus::Confirmed)->create([
            'destination' => 'Melbourne',
            'amount' => 7790,
            'notes' => 'Internal: supplier quote pending',
        ])->fresh();
        $otherBooking = Booking::factory()->create()->fresh();

        $this->actingAs($client)
            ->get(route('client.bookings.show', $booking->reference))
            ->assertOk()
            ->assertSee('BOOKING '.$booking->reference)
            ->assertSee('RM 7,790.00')
            ->assertSeeInOrder(['<li class="is-done is-current">', '✓</span>Confirmed', '<li class="">', '3</span>Completed'], false)
            ->assertDontSee('Internal: supplier quote pending');

        $this->get(route('client.bookings.show', $otherBooking->reference))->assertNotFound();
    }

    public function test_trip_request_reaches_the_admin_inbox_linked_to_the_client(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 09:00'));
        $client = User::factory()->create(['phone' => '+60123456789']);

        $this->actingAs($client)
            ->post(route('client.enquiries.store'), [
                'destination' => 'Melbourne',
                'preferred_date' => '2027-03-06',
                'adults' => 2,
                'children' => 1,
                'message' => 'Halal meals please.',
            ])
            ->assertRedirect(route('client.enquiries.index'))
            ->assertSessionHas('status');

        $enquiry = Enquiry::query()->sole();
        $this->assertTrue($enquiry->client->is($client));
        $this->assertSame(EnquirySource::Website, $enquiry->source);
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertSame([$client->name, $client->email, '+60123456789'], [$enquiry->name, $enquiry->email, $enquiry->phone]);
        $this->assertSame("Preferred travel date: 2027-03-06\nTravellers: 2 adult(s), 1 child(ren)\n\nHalal meals please.", $enquiry->message);

        $this->get(route('client.enquiries.index'))->assertSee('Melbourne')->assertSee('Received');
    }

    public function test_trip_request_needs_a_destination_and_a_future_date(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 09:00'));

        $this->actingAs(User::factory()->create())
            ->post(route('client.enquiries.store'), ['destination' => '', 'preferred_date' => '2026-01-01', 'adults' => 0, 'message' => ''])
            ->assertSessionHasErrors([
                'destination',
                'preferred_date' => 'Choose a travel date from today onwards.',
                'adults',
                'message',
            ]);

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_request_form_can_start_from_one_of_the_clients_bookings(): void
    {
        $client = User::factory()->create();
        $booking = Booking::factory()->for($client, 'client')->create(['destination' => 'Melbourne'])->fresh();
        $otherBooking = Booking::factory()->create(['destination' => 'Private Trip'])->fresh();

        $this->actingAs($client)
            ->get(route('client.enquiries.index', ['booking' => $booking->reference]))
            ->assertSee('Ask about booking '.$booking->reference)
            ->assertSee("About my booking {$booking->reference}: ");

        $this->get(route('client.enquiries.index', ['booking' => $otherBooking->reference]))
            ->assertSee('Plan a new trip')
            ->assertDontSee($otherBooking->reference);
    }

    public function test_client_updates_their_details_but_not_their_email(): void
    {
        $client = User::factory()->create(['email' => 'aisyah@example.com']);

        $this->actingAs($client)
            ->put(route('client.profile.update'), ['name' => 'Aisyah R.', 'phone' => '012-345 6789', 'email' => 'changed@example.com'])
            ->assertRedirect(route('client.profile.edit'));

        $client->refresh();
        $this->assertSame(['Aisyah R.', '+60123456789', 'aisyah@example.com'], [$client->name, $client->phone, $client->email]);

        $this->put(route('client.profile.update'), ['name' => 'Aisyah R.', 'phone' => 'call me'])
            ->assertSessionHasErrors(['phone' => 'Please enter a valid phone number, e.g. +60 12-345 6789.']);
    }

    public function test_client_changes_password_only_with_the_current_one(): void
    {
        $client = User::factory()->create();
        $this->actingAs($client);

        $this->put(route('client.password.update'), ['current_password' => 'wrong-password', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertSessionHasErrorsIn('password', ['current_password']);
        $this->assertTrue(Hash::check('password', $client->fresh()->password));

        $this->put(route('client.password.update'), ['current_password' => 'password', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertSessionHas('status', 'Your password has been changed.');
        $this->assertTrue(Hash::check('new-password-1', $client->fresh()->password));
    }

    public function test_first_sign_in_starts_the_guided_tour_on_the_homepage_until_it_is_finished(): void
    {
        $client = User::factory()->create();

        $this->post(route('client.login.store'), ['email' => $client->email, 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->get(route('home'))
            ->assertSee('data-tour-page="home"', false)
            ->assertSee('data-tour-autostart="true"', false);
        $this->get(route('client.profile.edit'))
            ->assertSee('data-tour-page="profile"', false)
            ->assertSee('data-tour-autostart="false"', false);

        $this->post(route('client.tour.complete'))->assertNoContent();
        $this->assertNotNull($client->fresh()->portal_tour_completed_at);

        $this->get(route('home'))->assertSee('data-tour-autostart="false"', false);
        $this->get(route('client.dashboard'))->assertSee('Take the tour');
    }

    public function test_guests_and_admins_get_no_guided_tour(): void
    {
        $this->get(route('home'))->assertDontSee('data-portal-tour', false);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('home'))
            ->assertDontSee('data-portal-tour', false);
    }

    public function test_profile_panel_links_to_the_account_portal(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertSee('href="'.route('client.dashboard').'"', false);
    }
}
