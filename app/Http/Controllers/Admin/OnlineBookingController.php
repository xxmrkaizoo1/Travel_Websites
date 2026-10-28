<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChinaMuslimPackage;
use App\Models\TourPackagePage;
use App\Support\BookablePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Opens or closes online booking for each Smart Muslim package and sets how many seats it offers.
 */
class OnlineBookingController extends Controller
{
    public function edit(): View
    {
        $tourPackages = TourPackagePage::query()
            ->withCount('bookings')
            ->orderBy('parent_slug')
            ->orderBy('name')
            ->get()
            ->map(fn (TourPackagePage $page): BookablePackage => new BookablePackage($page))
            ->filter(fn (BookablePackage $bookable): bool => $bookable->isBookable());

        $chinaPackages = ChinaMuslimPackage::query()
            ->withCount('bookings')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn (ChinaMuslimPackage $package): BookablePackage => new BookablePackage($package));

        $sections = [
            'series' => ['Series departures', 'Group trips with fixed departure dates. Clients pick a departure; fully booked and past dates are hidden automatically.', $tourPackages->filter(fn (BookablePackage $bookable): bool => $bookable->package->isSeries())],
            'private' => ['Private tour packages', 'Clients pick an itinerary and their own travel date.', $tourPackages->reject(fn (BookablePackage $bookable): bool => $bookable->package->isSeries())],
            'china' => ['China Muslim packages', 'Clients pick their travel date; the estimate uses the package price per person.', $chinaPackages],
        ];

        return view('Admin.online-booking.edit', [
            'sections' => collect($sections)->map(fn (array $section): array => [
                'title' => $section[0],
                'description' => $section[1],
                'rows' => $section[2]->map(fn (BookablePackage $bookable): array => $this->row($bookable))->values(),
            ])->filter(fn (array $section): bool => $section['rows']->isNotEmpty()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tour_packages' => ['nullable', 'array'],
            'tour_packages.*' => ['integer'],
            'china_packages' => ['nullable', 'array'],
            'china_packages.*' => ['integer'],
            'tour_packages_seats' => ['nullable', 'array'],
            'tour_packages_seats.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'tour_packages_seats_shown' => ['nullable', 'array'],
            'china_packages_seats' => ['nullable', 'array'],
            'china_packages_seats.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'china_packages_seats_shown' => ['nullable', 'array'],
            'departure_seats' => ['nullable', 'array'],
            'departure_seats.*' => ['array'],
            'departure_seats.*.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'departure_seats_shown' => ['nullable', 'array'],
        ], [
            'departure_seats.*.*.integer' => 'Available seats must be a whole number, or empty for no limit.',
            'departure_seats.*.*.min' => 'Available seats cannot be below zero.',
            'departure_seats.*.*.max' => 'Available seats cannot be more than 100,000.',
            '*_seats.*.integer' => 'Available seats must be a whole number, or empty for no limit.',
            '*_seats.*.min' => 'Available seats cannot be below zero.',
            '*_seats.*.max' => 'Available seats cannot be more than 100,000.',
        ]);

        $this->setOpen(TourPackagePage::class, $validated['tour_packages'] ?? []);
        $this->setOpen(ChinaMuslimPackage::class, $validated['china_packages'] ?? []);
        $this->setAvailableSeats(TourPackagePage::class, $validated['tour_packages_seats'] ?? [], $validated['tour_packages_seats_shown'] ?? []);
        $this->setAvailableSeats(ChinaMuslimPackage::class, $validated['china_packages_seats'] ?? [], $validated['china_packages_seats_shown'] ?? []);

        foreach ($validated['departure_seats'] ?? [] as $pageId => $departures) {
            $this->setDepartureSeats($pageId, $departures, $validated['departure_seats_shown'][$pageId] ?? []);
        }

        return redirect()->route('admin.online-booking.edit')->with('status', 'Online booking settings saved.');
    }

    /**
     * Opens or closes online booking for one package, from its edit page or list.
     */
    public function toggle(Request $request, BookablePackage $bookable): RedirectResponse
    {
        $open = $request->validate(['open' => ['required', 'boolean']])['open'];

        $bookable->package->forceFill(['booking_open' => (bool) $open])->save();

        return back()->with('status', ($open ? 'Online booking opened for ' : 'Online booking closed for ').$bookable->title().'.');
    }

    /**
     * @param  class-string<TourPackagePage|ChinaMuslimPackage>  $model
     * @param  array<int, int>  $openIds
     */
    private function setOpen(string $model, array $openIds): void
    {
        $model::query()->whereIn('id', $openIds)->update(['booking_open' => true]);
        $model::query()->whereNotIn('id', $openIds)->update(['booking_open' => false]);
    }

    /**
     * The admin types how many places are still available; an empty field means no limit. The package
     * stores the total (available plus already booked), so confirmed bookings keep counting down from it.
     * Only fields the admin changed are saved, so a page left open does not undo seats booked meanwhile.
     *
     * @param  class-string<TourPackagePage|ChinaMuslimPackage>  $model
     * @param  array<int|string, int|string|null>  $available
     * @param  array<int|string, int|string|null>  $shown
     */
    private function setAvailableSeats(string $model, array $available, array $shown): void
    {
        foreach ($available as $id => $value) {
            $value = $value === null || $value === '' ? null : (int) $value;
            $previous = ($shown[$id] ?? '') === '' ? null : (int) $shown[$id];

            if (array_key_exists($id, $shown) && $value === $previous) {
                continue;
            }

            $package = $model::query()->find($id);

            $package?->forceFill(['seats' => $value === null ? null : $value + (new BookablePackage($package))->seatsTaken()])->save();
        }
    }

    /**
     * Same as setAvailableSeats(), for each departure of a series package. Clearing a field removes the limit.
     *
     * @param  array<string, int|string|null>  $available
     * @param  array<string, int|string|null>  $shown
     */
    private function setDepartureSeats(int|string $pageId, array $available, array $shown): void
    {
        $page = TourPackagePage::query()->find($pageId);

        if ($page === null || ! $page->isSeries()) {
            return;
        }

        $bookable = new BookablePackage($page);
        $keys = array_column($bookable->departures(), 'key');

        foreach ($available as $key => $value) {
            $key = (string) $key;
            $value = $value === null || $value === '' ? null : (int) $value;
            $previous = ($shown[$key] ?? '') === '' ? null : (int) $shown[$key];

            if (! in_array($key, $keys, true) || (array_key_exists($key, $shown) && $value === $previous)) {
                continue;
            }

            if ($value === null) {
                $page->departureSeats()->where('departure_key', $key)->delete();

                continue;
            }

            $page->departureSeats()->updateOrCreate(['departure_key' => $key], ['seats' => $value + $bookable->seatsTaken($key)]);
        }
    }

    /**
     * @return array{id: int, field: string, name: string, context: string, search: string, seat_state: string, choices: string, bookings: int, open: bool, seats: ?int, seats_taken: int, seats_left: ?int, per_departure: bool, departures: list<array<string, mixed>>, page_url: string, booking_url: string}
     */
    private function row(BookablePackage $bookable): array
    {
        $package = $bookable->package;
        $options = collect($bookable->options());
        $departures = $bookable->departures();
        $context = $package instanceof TourPackagePage
            ? ($package->parent_slug ? Str::headline($package->parent_slug) : $package->sectionLabel())
            : $package->days.'D'.$package->nights.'N · from '.$package->formattedPrice();

        return [
            'id' => $package->id,
            'field' => $package instanceof TourPackagePage ? 'tour_packages' : 'china_packages',
            'name' => $bookable->title(),
            'context' => $context,
            // Text the filter bar searches: name, section or country, and every series destination.
            'search' => Str::lower(collect([$bookable->title(), $context, ...array_column($departures, 'destination')])->unique()->implode(' ')),
            'seat_state' => $this->seatState($bookable, $departures),
            'choices' => $this->choicesSummary($package, $options),
            'bookings' => (int) $package->bookings_count,
            'open' => $bookable->isOpen(),
            'seats' => $bookable->seats(),
            'seats_taken' => $bookable->seatsTaken(),
            'seats_left' => $bookable->seatsLeft(),
            'per_departure' => $bookable->hasSeatsPerDeparture(),
            'departures' => $departures,
            'page_url' => $bookable->pageUrl(),
            'booking_url' => $bookable->bookingUrl(),
        ];
    }

    /**
     * "full" when nothing can be booked for lack of seats, "limited" when some seats are set, otherwise "none".
     *
     * @param  list<array<string, mixed>>  $departures
     */
    private function seatState(BookablePackage $bookable, array $departures): string
    {
        if ($bookable->isFull()) {
            return 'full';
        }

        $limited = $bookable->hasSeatsPerDeparture()
            ? collect($departures)->contains(fn (array $departure): bool => $departure['seats'] !== null)
            : $bookable->seats() !== null;

        return $limited ? 'limited' : 'none';
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $options
     */
    private function choicesSummary(TourPackagePage|ChinaMuslimPackage $package, Collection $options): string
    {
        if ($package instanceof ChinaMuslimPackage) {
            return 'Any date';
        }

        if ($package->isSeries()) {
            $destinations = $options->pluck('group')->unique()->count();
            $departures = $options->whereNotNull('travel_date')->count();

            return Str::plural('destination', $destinations, true).' · '.Str::plural('open departure', $departures, true);
        }

        return Str::plural('itinerary', $options->count(), true);
    }
}
