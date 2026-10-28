<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ChinaMuslimPackage;
use App\Models\TourPackagePage;
use App\Models\User;
use App\Support\BookablePackage;
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

    public function test_confirmed_bookings_take_seats_until_the_package_is_full(): void
    {
        $package = ChinaMuslimPackage::factory()->create(['title' => 'XINJIANG MUSLIM TOURS', 'price' => 3990, 'seats' => 5]);
        $routeKey = 'china-muslim-package/'.$package->id;
        $seats = fn (): ?int => (new BookablePackage($package->fresh()))->seatsLeft();
        $client = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $confirm = fn (Booking $booking, string $status = 'confirmed') => $this->actingAs($admin)->put(route('admin.bookings.update', $booking), [
            'user_id' => $booking->user_id,
            'destination' => $booking->destination,
            'travel_date' => $booking->travel_date->toDateString(),
            'adults' => $booking->adults,
            'children' => $booking->children,
            'amount' => '1000',
            'status' => $status,
        ]);

        // A request does not hold seats; confirming it takes one seat per adult and child.
        $this->actingAs($client)->get(route('client.package-bookings.create', $routeKey))->assertSee('5 seats available');
        $this->post(route('client.package-bookings.store', $routeKey), ['option' => 'package', 'travel_date' => '2026-11-20', 'adults' => 2, 'children' => 1])->assertSessionHasNoErrors();
        $first = Booking::query()->latest('id')->first();
        $this->assertSame(5, $seats());

        $this->actingAs($admin)->get(route('admin.bookings.edit', $first))->assertSee('Available seats:</strong> 5 on this package', false);
        $confirm($first)->assertSessionHasNoErrors();
        $this->assertSame(2, $seats());
        $confirm($first->fresh())->assertSessionHasNoErrors();
        $this->assertSame(2, $seats(), 'Saving a confirmed booking again does not take its seats twice.');

        // Clients cannot ask for more travellers than seats left.
        $this->actingAs($client)->post(route('client.package-bookings.store', $routeKey), ['option' => 'package', 'travel_date' => '2026-11-20', 'adults' => 3])
            ->assertSessionHasErrors(['adults' => 'Only 2 seats available on this package. Please book for fewer travellers or contact us.']);
        $this->post(route('client.package-bookings.store', $routeKey), ['option' => 'package', 'travel_date' => '2026-11-20', 'adults' => 2])->assertSessionHasNoErrors();
        $this->post(route('client.package-bookings.store', $routeKey), ['option' => 'package', 'travel_date' => '2026-11-21', 'adults' => 1])->assertSessionHasNoErrors();
        [$second, $third] = Booking::query()->where('id', '>', $first->id)->orderBy('id')->get()->all();

        $confirm($second)->assertSessionHasNoErrors();
        $this->assertSame(0, $seats());
        $this->assertNull((new BookablePackage($package->fresh()))->bookNowUrl());
        $this->actingAs($client)->get(route('client.package-bookings.create', $routeKey))->assertSee('This package is fully booked');

        // The admin cannot confirm past the limit; cancelling frees the seats again.
        $confirm($third)->assertSessionHasErrors(['status' => 'This package is fully booked, so the booking cannot be confirmed. Raise the available seats on the Online booking page first.']);
        $third->forceFill(['amount' => 3990])->save();
        $this->actingAs($admin)->patchJson(route('admin.bookings.status', $third), ['status' => 'confirmed'])
            ->assertJsonValidationErrors(['status' => 'This package is fully booked, so the booking cannot be confirmed. Raise the available seats on the Online booking page first.']);
        $this->assertSame(BookingStatus::Pending, $third->fresh()->status);

        $confirm($first->fresh(), 'cancelled')->assertSessionHasNoErrors();
        $this->assertSame(3, $seats());
        $confirm($third)->assertSessionHasNoErrors();
        $this->assertSame(2, $seats());
    }

    public function test_admin_sets_available_seats_for_each_package(): void
    {
        $private = $this->privatePackagePage();
        $china = ChinaMuslimPackage::factory()->create(['title' => 'NINGXIA MUSLIM TOURS', 'seats' => 20]);
        $confirmed = Booking::factory()->status(BookingStatus::Confirmed)->create(['adults' => 3, 'children' => 1]);
        $confirmed->bookable()->associate($china)->save();
        $available = fn (TourPackagePage|ChinaMuslimPackage $package): ?int => (new BookablePackage($package->fresh()))->seatsLeft();
        $this->actingAs(User::factory()->admin()->create());

        // The field shows the places still available, not the original total.
        $this->get(route('admin.online-booking.edit'))
            ->assertOk()
            ->assertSee('name="china_packages_seats['.$china->id.']" value="16"', false)
            ->assertSee('4 booked of 20');

        // Typing a number makes exactly that many places available, on top of those already booked.
        $this->put(route('admin.online-booking.update'), [
            'tour_packages' => [$private->id],
            'china_packages' => [$china->id],
            'tour_packages_seats' => [$private->id => '30'],
            'tour_packages_seats_shown' => [$private->id => ''],
            'china_packages_seats' => [$china->id => '10'],
            'china_packages_seats_shown' => [$china->id => '16'],
        ])->assertRedirect(route('admin.online-booking.edit'));

        $this->assertSame([30, 10], [$available($private), $available($china)]);
        $this->assertSame(14, $china->fresh()->seats);

        // A field left as it was is not saved again, so seats confirmed meanwhile still count down.
        $another = Booking::factory()->status(BookingStatus::Confirmed)->create(['adults' => 2, 'children' => 0]);
        $another->bookable()->associate($china)->save();
        $this->put(route('admin.online-booking.update'), [
            'china_packages' => [$china->id],
            'china_packages_seats' => [$china->id => '10'],
            'china_packages_seats_shown' => [$china->id => '10'],
        ])->assertRedirect(route('admin.online-booking.edit'));
        $this->assertSame(8, $available($china));

        $this->put(route('admin.online-booking.update'), [
            'china_packages_seats' => [$china->id => ''],
            'china_packages_seats_shown' => [$china->id => '8'],
        ]);
        $this->assertNull($china->fresh()->seats);

        $this->put(route('admin.online-booking.update'), ['china_packages_seats' => [$china->id => '-3']])
            ->assertSessionHasErrors(['china_packages_seats.'.$china->id => 'Available seats cannot be below zero.']);
    }

    public function test_each_series_departure_has_its_own_available_seats(): void
    {
        $page = $this->seriesPage();
        $data = $page->package_data;
        $data['series'][0]['package_details']['departures'][] = ['date' => '15 Jun 2027', 'price' => 'RM 7,990', 'availability' => 'Open'];
        $page->forceFill(['package_data' => $data])->save();
        $routeKey = 'tour-package/'.$page->slug;
        $april = BookablePackage::departureKey('Melbourne', '26 Apr 2027');
        $june = BookablePackage::departureKey('Melbourne', '15 Jun 2027');
        $left = fn (string $key): ?int => (new BookablePackage($page->fresh()))->seatsLeft($key);
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create();

        // The admin sees every departure of the series package and sets seats on one of them.
        $this->actingAs($admin)->get(route('admin.online-booking.edit'))
            ->assertSee('Available seats for each departure')
            ->assertSee('name="departure_seats['.$page->id.']['.$april.']"', false)
            ->assertSee('name="departure_seats['.$page->id.']['.$june.']"', false);

        $this->put(route('admin.online-booking.update'), [
            'tour_packages' => [$page->id],
            'departure_seats' => [$page->id => [$april => '4', $june => '']],
            'departure_seats_shown' => [$page->id => [$april => '', $june => '']],
        ])->assertSessionHasNoErrors();
        $this->assertSame([4, null], [$left($april), $left($june)]);

        // The client sees the seats on that departure, and the booking remembers which departure it is for.
        $this->actingAs($client)->get(route('client.package-bookings.create', $routeKey))->assertSee('26 Apr 2027 · RM 7,790 · 4 seats left');
        $this->post(route('client.package-bookings.store', $routeKey), ['option' => 'melbourne-1', 'adults' => 3])->assertSessionHasNoErrors();
        $booking = Booking::query()->latest('id')->first();
        $this->assertSame($april, $booking->bookable_option);
        $this->assertSame(4, $left($april), 'A request does not take seats until it is confirmed.');

        $this->actingAs($admin)->put(route('admin.bookings.update', $booking), [
            'user_id' => $booking->user_id,
            'destination' => $booking->destination,
            'travel_date' => '2027-04-26',
            'adults' => 3,
            'amount' => '23370',
            'status' => 'confirmed',
        ])->assertSessionHasNoErrors();
        $this->assertSame([1, null], [$left($april), $left($june)]);
        $this->get(route('tour-package.show', $page->slug))->assertSee('1 seat left');

        $this->actingAs($client)->post(route('client.package-bookings.store', $routeKey), ['option' => 'melbourne-1', 'adults' => 2])
            ->assertSessionHasErrors(['adults' => 'Only 1 seat available on this departure. Please book for fewer travellers or contact us.']);

        // When the departure is full it disappears from the form; the other departure stays bookable.
        $this->actingAs($admin)->put(route('admin.online-booking.update'), [
            'tour_packages' => [$page->id],
            'departure_seats' => [$page->id => [$april => '0']],
            'departure_seats_shown' => [$page->id => [$april => '1']],
        ])->assertSessionHasNoErrors();
        $this->assertSame(0, $left($april));
        $this->get(route('tour-package.show', $page->slug))->assertSee('Fully booked');

        $this->actingAs($client)->post(route('client.package-bookings.store', $routeKey), ['option' => 'melbourne-1', 'adults' => 1])
            ->assertSessionHasErrors(['option' => 'That departure is no longer available. Please choose another one.']);
        $this->post(route('client.package-bookings.store', $routeKey), ['option' => 'melbourne-3', 'adults' => 5])->assertSessionHasNoErrors();
        $this->assertSame($june, Booking::query()->latest('id')->first()->bookable_option);
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
