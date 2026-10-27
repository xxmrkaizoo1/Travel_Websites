<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\EnquiryStatus;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AdminTravelOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_clients_cannot_open_the_overview(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_stat_cards_are_calculated_from_bookings_clients_and_enquiries(): void
    {
        $this->seedBusiness();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', function (array $stats): bool {
                [$bookings, $revenue, $clients, $enquiries] = $stats;

                return $bookings['value'] === '5' && $bookings['trend'] === 300.0
                    && $revenue['value'] === 'RM 6,000' && $revenue['trend'] === 100.0
                    && $clients['value'] === '5' && $clients['foot'] === '+6 new this month'
                    && $enquiries['value'] === '3' && $enquiries['foot'] === '2 new today';
            })
            ->assertDontSee('Nur Aisyah')
            ->assertDontSee('Sample dashboard data');
    }

    public function test_popular_destinations_rank_bookings_and_leave_out_cancelled_trips(): void
    {
        $this->seedBusiness();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertViewHas('popularDestinations', fn (Collection $destinations): bool => $destinations->map(fn (array $row): array => [$row['name'], $row['bookings'], $row['revenue']])->all() === [
                ['Bali', 2, 4000.0],
                ['Seoul', 2, 2000.0],
            ]);

        $this->get(route('admin.destinations.index'))
            ->assertOk()
            ->assertSeeInOrder(['Bali', 'RM 4,000.00', 'Seoul', 'RM 2,000.00'])
            ->assertDontSee('Tokyo');
    }

    public function test_revenue_chart_totals_the_selected_period(): void
    {
        $this->seedBusiness();
        $admin = User::factory()->admin()->create();

        $totals = collect(['7d' => 4000.0, '30d' => 6000.0, '12m' => 6000.0, 'forever' => 4000.0])
            ->map(fn (float $expected, string $range): bool => $this->actingAs($admin)->get(route('admin.dashboard', ['range' => $range]))->viewData('chart')['total'] === $expected);

        $this->assertSame(['7d' => true, '30d' => true, '12m' => true, 'forever' => true], $totals->all());
    }

    public function test_recent_bookings_link_to_their_client_and_booking(): void
    {
        $booking = Booking::factory()->create(['destination' => 'Melbourne']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertSee($booking->fresh()->reference)
            ->assertSee($booking->client->name)
            ->assertSee('href="'.route('admin.bookings.edit', $booking).'"', false)
            ->assertSee('href="'.route('admin.clients.show', $booking->client).'"', false)
            ->assertSee('Showing <strong>1</strong> of 1 bookings', false);
    }

    /**
     * Five bookings (two earning revenue this month, one last month, one pending, one cancelled),
     * a suspended client, an admin and four enquiries, as of 15 Oct 2026.
     */
    private function seedBusiness(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 12:00'));

        $booking = fn (string $destination, BookingStatus $status, int $amount, string $bookedAt) => Booking::factory()
            ->status($status)
            ->create(['destination' => $destination, 'amount' => $amount, 'adults' => 2, 'created_at' => $bookedAt]);

        $booking('Bali', BookingStatus::Confirmed, 3000, '2026-10-14 09:00');
        $booking('Bali', BookingStatus::Completed, 1000, '2026-10-13 09:00');
        $booking('Seoul', BookingStatus::Confirmed, 2000, '2026-09-20 09:00');
        $booking('Seoul', BookingStatus::Pending, 9999, '2026-10-02 09:00');
        $booking('Tokyo', BookingStatus::Cancelled, 7777, '2026-10-03 09:00');

        User::factory()->suspended()->create();
        User::factory()->admin()->create();

        Enquiry::factory()->count(2)->create();
        Enquiry::factory()->status(EnquiryStatus::InProgress)->create(['created_at' => '2026-10-01 09:00']);
        Enquiry::factory()->status(EnquiryStatus::Closed)->create();
    }
}
