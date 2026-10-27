<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ChinaMuslimPackage;
use App\Models\TourPackagePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PackageBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-15 09:00'));
    }

    public function test_guests_sign_in_first_and_come_back_to_the_booking_form(): void
    {
        $page = $this->seriesPage();
        $client = User::factory()->create();
        $bookingUrl = route('client.package-bookings.create', 'tour-package/'.$page->slug);

        $this->get($bookingUrl)->assertRedirect(route('client.login'));

        $this->post(route('client.login.store'), ['email' => $client->email, 'password' => 'password'])
            ->assertRedirect($bookingUrl);
    }

    public function test_client_books_a_series_departure_on_its_fixed_date(): void
    {
        $page = $this->seriesPage();
        $client = User::factory()->create();

        $this->actingAs($client)
            ->get(route('client.package-bookings.create', ['bookable' => 'tour-package/'.$page->slug, 'option' => 'melbourne-1']))
            ->assertOk()
            ->assertSee('<option value="melbourne-1" data-date="2027-04-26" data-price="7790" selected', false)
            ->assertDontSee('melbourne-0')
            ->assertDontSee('melbourne-2');

        $response = $this->post(route('client.package-bookings.store', 'tour-package/'.$page->slug), [
            'option' => 'melbourne-1',
            'travel_date' => '2027-01-01',
            'adults' => 2,
            'children' => 1,
            'special_requests' => 'Halal meals and an adjoining room.',
        ]);

        $booking = Booking::query()->sole();
        $response->assertRedirect(route('client.bookings.show', $booking->reference));

        $this->assertTrue($booking->client->is($client));
        $this->assertTrue($booking->bookable->is($page));
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame(['Melbourne', 'Series Departures 2030 · Melbourne 7D6N'], [$booking->destination, $booking->package_name]);
        $this->assertSame(['2027-04-26', '2027-05-02'], [$booking->travel_date->toDateString(), $booking->return_date->toDateString()]);
        $this->assertSame('23370.00', $booking->amount);

        $this->get(route('client.bookings.show', $booking->reference))
            ->assertSee('Estimated total')
            ->assertSee('RM 23,370.00')
            ->assertSee('Halal meals and an adjoining room.');
    }

    public function test_fully_booked_and_past_departures_cannot_be_booked(): void
    {
        $page = $this->seriesPage();
        $this->actingAs(User::factory()->create());

        foreach (['melbourne-0', 'melbourne-2'] as $unavailable) {
            $this->post(route('client.package-bookings.store', 'tour-package/'.$page->slug), ['option' => $unavailable, 'adults' => 2])
                ->assertSessionHasErrors(['option' => 'That departure is no longer available. Please choose another one.']);
        }

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_private_itinerary_needs_a_travel_date_from_today(): void
    {
        $page = $this->privatePackagePage();
        $this->actingAs(User::factory()->create());
        $store = route('client.package-bookings.store', 'tour-package/'.$page->slug);

        $this->post($store, ['option' => 'itinerary-0', 'adults' => 2])
            ->assertSessionHasErrors(['travel_date' => 'Choose the date you would like to travel.']);
        $this->post($store, ['option' => 'itinerary-0', 'travel_date' => '2026-10-01', 'adults' => 2])
            ->assertSessionHasErrors(['travel_date' => 'Choose a travel date from today onwards.']);

        $this->post($store, ['option' => 'itinerary-0', 'travel_date' => '2026-12-10', 'adults' => 1]);

        $booking = Booking::query()->sole();
        $this->assertSame(['Langkawi', 'Signature package'], [$booking->destination, $booking->package_name]);
        $this->assertSame(['2026-12-10', '2026-12-12'], [$booking->travel_date->toDateString(), $booking->return_date->toDateString()]);
        $this->assertNull($booking->amount);
    }

    public function test_china_muslim_package_estimates_from_the_price_per_person(): void
    {
        $package = ChinaMuslimPackage::factory()->create(['title' => 'XINJIANG MUSLIM TOURS', 'price' => 3990, 'days' => 8, 'nights' => 7]);

        $this->actingAs(User::factory()->create())
            ->post(route('client.package-bookings.store', 'china-muslim-package/'.$package->id), ['option' => 'package', 'travel_date' => '2026-11-20', 'adults' => 2]);

        $booking = Booking::query()->sole();
        $this->assertTrue($booking->bookable->is($package));
        $this->assertSame(['China', 'XINJIANG MUSLIM TOURS', '7980.00', '2026-11-27'], [$booking->destination, $booking->package_name, $booking->amount, $booking->return_date->toDateString()]);
    }

    public function test_closed_package_hides_book_now_and_refuses_bookings(): void
    {
        $page = $this->privatePackagePage();
        $bookingUrl = route('client.package-bookings.create', ['bookable' => 'tour-package/'.$page->slug, 'option' => 'itinerary-0']);

        $this->get(route('tour-package.show', $page->slug))->assertSee('href="'.$bookingUrl.'"', false);

        $page->forceFill(['booking_open' => false])->save();
        $client = User::factory()->create();

        $this->get(route('tour-package.show', $page->slug))->assertDontSee($bookingUrl, false);
        $this->actingAs($client)->get($bookingUrl)->assertOk()->assertSee('Online booking is closed for this package');
        $this->post(route('client.package-bookings.store', 'tour-package/'.$page->slug), ['option' => 'itinerary-0', 'travel_date' => '2026-12-10', 'adults' => 1])
            ->assertSessionHasErrors('option');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_pages_without_anything_to_book_are_not_found(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('client.package-bookings.create', 'tour-package/thailand'))
            ->assertNotFound();
        $this->get(route('client.package-bookings.create', 'china-muslim-package/999'))->assertNotFound();
    }

    public function test_admin_opens_and_closes_online_booking(): void
    {
        $series = $this->seriesPage();
        $private = $this->privatePackagePage();
        $china = ChinaMuslimPackage::factory()->create(['title' => 'NINGXIA MUSLIM TOURS']);
        $admin = User::factory()->admin()->create();

        $this->actingAs(User::factory()->create())->get(route('admin.online-booking.edit'))->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.online-booking.edit'))
            ->assertOk()
            ->assertSeeInOrder(['Series departures', 'Series Departures 2030', 'Private tour packages', 'Langkawi', 'China Muslim packages', 'NINGXIA MUSLIM TOURS'])
            ->assertViewHas('sections', fn ($sections): bool => $sections->flatMap(fn (array $section) => $section['rows']->pluck('name'))->doesntContain('Thailand'));

        $this->put(route('admin.online-booking.update'), ['tour_packages' => [$series->id]])
            ->assertRedirect(route('admin.online-booking.edit'));

        $this->assertTrue($series->fresh()->booking_open);
        $this->assertFalse($private->fresh()->booking_open);
        $this->assertFalse($china->fresh()->booking_open);
    }

    public function test_admin_switches_booking_from_a_package_edit_page(): void
    {
        $page = $this->privatePackagePage();
        $china = ChinaMuslimPackage::factory()->create(['title' => 'NINGXIA MUSLIM TOURS']);
        $toggle = route('admin.online-booking.toggle', 'tour-package/'.$page->slug);

        $this->actingAs(User::factory()->create())->put($toggle, ['open' => 0])->assertForbidden();
        $this->assertTrue($page->fresh()->booking_open);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.tour-packages.edit', $page))
            ->assertSee('action="'.$toggle.'"', false)
            ->assertSee('Booking open');

        $this->from(route('admin.tour-packages.edit', $page))
            ->put($toggle, ['open' => 0])
            ->assertRedirect(route('admin.tour-packages.edit', $page))
            ->assertSessionHas('status', 'Online booking closed for Langkawi.');
        $this->assertFalse($page->fresh()->booking_open);

        $this->put(route('admin.online-booking.toggle', 'china-muslim-package/'.$china->id), ['open' => 0]);
        $this->assertFalse($china->fresh()->booking_open);
        $this->get(route('admin.china-muslim'))->assertSee('Booking closed');

        $this->put(route('admin.online-booking.toggle', 'china-muslim-package/'.$china->id), ['open' => 1]);
        $this->assertTrue($china->fresh()->booking_open);
    }

    public function test_admin_confirms_an_online_booking_only_with_a_final_amount(): void
    {
        $booking = Booking::factory()->create(['amount' => null, 'status' => BookingStatus::Pending, 'special_requests' => 'Wheelchair access']);
        $booking->bookable()->associate($this->privatePackagePage())->save();
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.bookings.index'))->assertSee('Online')->assertSee('To be quoted');
        $this->get(route('admin.bookings.edit', $booking))->assertSee('Booked online by the client')->assertSee('Wheelchair access');

        $payload = [
            'user_id' => $booking->user_id,
            'destination' => $booking->destination,
            'travel_date' => '2026-12-10',
            'adults' => 2,
            'amount' => '',
            'status' => 'confirmed',
        ];

        $this->put(route('admin.bookings.update', $booking), $payload)
            ->assertSessionHasErrors(['amount' => 'Enter the final amount before confirming or completing the booking.']);
        $this->put(route('admin.bookings.update', $booking), [...$payload, 'amount' => '4200'])->assertSessionHasNoErrors();

        $this->assertSame([BookingStatus::Confirmed, '4200.00'], [$booking->fresh()->status, $booking->fresh()->amount]);
    }

    private function seriesPage(): TourPackagePage
    {
        $content = TourPackagePage::starterContent(TourPackagePage::KIND_INTRODUCTION, 'Series Departures 2030', 'series');
        $content['package_data']['series'][0] = [
            ...$content['package_data']['series'][0],
            'name' => 'Melbourne',
            'package_details' => [
                'headline' => 'Melbourne 7D6N',
                'duration' => '7D6N',
                'departures' => [
                    ['date' => '6 Oct 2026', 'price' => 'RM 7,790', 'availability' => '10 seats left'],
                    ['date' => '26 Apr 2027', 'price' => 'RM 7,790', 'availability' => 'Promo'],
                    ['date' => '22 May 2027', 'price' => 'RM 7,790', 'availability' => 'Fully booked'],
                ],
            ],
        ];

        return $this->customPage('series-2030', $content);
    }

    private function privatePackagePage(): TourPackagePage
    {
        return $this->customPage('langkawi', TourPackagePage::starterContent(TourPackagePage::KIND_PACKAGE, 'Langkawi'));
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function customPage(string $slug, array $content): TourPackagePage
    {
        $page = new TourPackagePage($content);
        $page->forceFill(['slug' => $slug, 'is_custom' => true])->save();

        return $page->fresh();
    }
}
