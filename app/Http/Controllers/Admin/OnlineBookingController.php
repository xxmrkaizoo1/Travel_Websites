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
 * Opens or closes online booking for each Smart Muslim package.
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
        ]);

        $this->setOpen(TourPackagePage::class, $validated['tour_packages'] ?? []);
        $this->setOpen(ChinaMuslimPackage::class, $validated['china_packages'] ?? []);

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
     * @return array{id: int, field: string, name: string, context: string, choices: string, bookings: int, open: bool, page_url: string, booking_url: string}
     */
    private function row(BookablePackage $bookable): array
    {
        $package = $bookable->package;
        $options = collect($bookable->options());

        return [
            'id' => $package->id,
            'field' => $package instanceof TourPackagePage ? 'tour_packages' : 'china_packages',
            'name' => $bookable->title(),
            'context' => $package instanceof TourPackagePage
                ? ($package->parent_slug ? Str::headline($package->parent_slug) : $package->sectionLabel())
                : $package->days.'D'.$package->nights.'N · from '.$package->formattedPrice(),
            'choices' => $this->choicesSummary($package, $options),
            'bookings' => (int) $package->bookings_count,
            'open' => $bookable->isOpen(),
            'page_url' => $bookable->pageUrl(),
            'booking_url' => $bookable->bookingUrl(),
        ];
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
