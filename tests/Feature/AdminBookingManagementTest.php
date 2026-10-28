<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\EnquiryStatus;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminBookingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_manage_bookings(): void
    {
        $booking = Booking::factory()->create();

        $this->get(route('admin.bookings.index'))->assertRedirect(route('login'));
        $this->post(route('admin.bookings.store'), [])->assertRedirect(route('login'));
        $this->delete(route('admin.bookings.destroy', $booking))->assertRedirect(route('login'));
        $this->assertModelExists($booking);
    }

    public function test_admin_changes_a_status_from_the_full_screen_view(): void
    {
        $quoted = Booking::factory()->status(BookingStatus::Pending)->create(['amount' => 2400]);
        $unquoted = Booking::factory()->status(BookingStatus::Pending)->create(['amount' => null]);

        $this->patchJson(route('admin.bookings.status', $quoted), ['status' => 'confirmed'])->assertUnauthorized();
        $this->actingAs(User::factory()->create())->patchJson(route('admin.bookings.status', $quoted), ['status' => 'confirmed'])->assertForbidden();

        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.dashboard'))
            ->assertSee('data-booking-manager-open', false)
            ->assertSee('Manage bookings')
            ->assertSee('data-url="'.route('admin.bookings.status', $quoted).'"', false);

        $this->patchJson(route('admin.bookings.status', $quoted), ['status' => 'confirmed'])
            ->assertOk()
            ->assertJson(['status' => 'confirmed', 'label' => 'Confirmed', 'message' => "Booking {$quoted->fresh()->reference} is now Confirmed."]);
        $this->assertSame(BookingStatus::Confirmed, $quoted->fresh()->status);

        // Same rules as the edit page: a final amount before confirming, and only known statuses.
        $this->patchJson(route('admin.bookings.status', $unquoted), ['status' => 'completed'])
            ->assertJsonValidationErrors(['status' => 'Enter the final amount before confirming or completing. Open the booking to add it.']);
        $this->patchJson(route('admin.bookings.status', $unquoted), ['status' => 'cancelled'])->assertOk();
        $this->patchJson(route('admin.bookings.status', $unquoted), ['status' => 'lost'])->assertJsonValidationErrors('status');
        $this->assertSame(BookingStatus::Cancelled, $unquoted->fresh()->status);
    }

    public function test_admin_creates_a_booking_with_a_generated_reference(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 12:00'));
        $client = User::factory()->create(['name' => 'Aisyah Rahman']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.bookings.create'))
            ->assertOk()
            ->assertSee('<option value="'.$client->id.'" >Aisyah Rahman · '.$client->email.'</option>', false);

        $response = $this->post(route('admin.bookings.store'), $this->payload($client, ['children' => '']));

        $booking = Booking::query()->sole();
        $this->assertSame('BK-2026-'.str_pad((string) $booking->id, 4, '0', STR_PAD_LEFT), $booking->reference);
        $response->assertRedirect(route('admin.bookings.index'))
            ->assertSessionHas('status', "Booking {$booking->reference} created.");
        $this->assertTrue($booking->client->is($client));
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame('8450.00', $booking->amount);
        $this->assertSame(0, $booking->children);
        $this->assertSame('2026-12-01', $booking->travel_date->toDateString());
    }

    public function test_booking_must_belong_to_a_client_and_have_sensible_dates(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.bookings.store'), $this->payload($admin, ['return_date' => '2026-11-30']))
            ->assertSessionHasErrors([
                'user_id' => 'Choose a client account from the list.',
                'return_date' => 'The return date cannot be before the travel date.',
            ]);

        $this->post(route('admin.bookings.store'), $this->payload($client, ['status' => 'refunded', 'adults' => 0]))
            ->assertSessionHasErrors(['status', 'adults']);

        $this->post(route('admin.bookings.store'), [])
            ->assertSessionHasErrors(['user_id' => 'Choose the client this booking is for.', 'destination', 'travel_date', 'adults', 'status']);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_an_enquiry_becomes_a_booking_and_is_closed(): void
    {
        $client = User::factory()->create();
        $enquiry = Enquiry::factory()->create(['email' => $client->email, 'destination' => 'Melbourne', 'message' => 'Family of four in March.']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.bookings.create', ['enquiry' => $enquiry->id]))
            ->assertOk()
            ->assertSee('name="enquiry_id" value="'.$enquiry->id.'"', false)
            ->assertSee('value="Melbourne"', false)
            ->assertSee('From enquiry: Family of four in March.')
            ->assertSee('<option value="'.$client->id.'" selected>', false);

        $this->post(route('admin.bookings.store'), $this->payload($client, ['enquiry_id' => $enquiry->id]))->assertRedirect();

        $enquiry->refresh();
        $this->assertSame(EnquiryStatus::Closed, $enquiry->status);
        $this->assertTrue($enquiry->booking->is(Booking::query()->sole()));
        $this->assertTrue($enquiry->client->is($client));

        $this->post(route('admin.bookings.store'), $this->payload($client, ['enquiry_id' => $enquiry->id]))
            ->assertSessionHasErrors(['enquiry_id' => 'That enquiry has already been turned into a booking.']);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_bookings_list_filters_by_status_and_search(): void
    {
        $bali = Booking::factory()->status(BookingStatus::Confirmed)->create(['destination' => 'Bali']);
        $seoul = Booking::factory()->status(BookingStatus::Pending)->create(['destination' => 'Seoul']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.bookings.index', ['status' => 'confirmed']))
            ->assertOk()
            ->assertSee($bali->fresh()->reference)
            ->assertDontSee($seoul->fresh()->reference);

        $this->get(route('admin.bookings.index', ['search' => $seoul->client->email]))
            ->assertSee($seoul->fresh()->reference)
            ->assertDontSee($bali->fresh()->reference);

        $this->get(route('admin.bookings.index', ['status' => 'unknown']))->assertSessionHasErrors('status');
    }

    public function test_admin_updates_and_deletes_a_booking(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.bookings.edit', $booking))
            ->assertOk()
            ->assertSee('Booking '.$booking->fresh()->reference);

        $this->put(route('admin.bookings.update', $booking), $this->payload($booking->client, ['status' => 'cancelled', 'notes' => 'Client changed plans.']))
            ->assertRedirect(route('admin.bookings.edit', $booking));
        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertSame('Client changed plans.', $booking->fresh()->notes);

        $this->delete(route('admin.bookings.destroy', $booking))->assertRedirect(route('admin.bookings.index'));
        $this->assertModelMissing($booking);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(User $client, array $overrides = []): array
    {
        return [
            'user_id' => $client->id,
            'destination' => 'Bali',
            'package_name' => 'Bali Honeymoon 5D4N',
            'travel_date' => '2026-12-01',
            'return_date' => '2026-12-05',
            'adults' => 2,
            'children' => 0,
            'amount' => '8450',
            'status' => 'confirmed',
            'notes' => '',
            ...$overrides,
        ];
    }
}
