<?php

namespace App\Http\Controllers\Client;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\BookablePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Online booking for Smart Muslim packages. A booking starts as "Requested" (pending)
 * until the team confirms availability and the final price.
 */
class PackageBookingController extends Controller
{
    public function create(Request $request, BookablePackage $bookable): View
    {
        $options = $bookable->options();
        $selected = $bookable->option($request->query('option'))['key']
            ?? $bookable->firstOptionKeyFor((string) $request->query('destination'))
            ?? ($options[0]['key'] ?? null);

        return view('client.portal.book', [
            'bookable' => $bookable,
            'options' => $options,
            'selectedOption' => old('option', $selected),
        ]);
    }

    public function store(Request $request, BookablePackage $bookable): RedirectResponse
    {
        if (! $bookable->isOpen()) {
            throw ValidationException::withMessages([
                'option' => 'Online booking for this package is closed at the moment. Please contact us to book.',
            ]);
        }

        $validated = $request->validate([
            'option' => ['required', 'string', Rule::in(array_column($bookable->options(), 'key'))],
            'travel_date' => ['nullable', 'date', 'after_or_equal:today'],
            'adults' => ['required', 'integer', 'min:1', 'max:50'],
            'children' => ['nullable', 'integer', 'min:0', 'max:50'],
            'special_requests' => ['nullable', 'string', 'max:1500'],
        ], [
            'option.required' => 'Choose the package or departure you would like to book.',
            'option.in' => 'That departure is no longer available. Please choose another one.',
            'travel_date.after_or_equal' => 'Choose a travel date from today onwards.',
        ]);

        $option = $bookable->option($validated['option']);
        $travelDate = $option['travel_date'] ?? $validated['travel_date'] ?? null;

        if ($travelDate === null) {
            throw ValidationException::withMessages(['travel_date' => 'Choose the date you would like to travel.']);
        }

        $travelDate = Carbon::parse($travelDate);
        $children = (int) ($validated['children'] ?? 0);
        $seatsLeft = $option['seats_left'];

        if ($seatsLeft !== null && $seatsLeft < $validated['adults'] + $children) {
            throw ValidationException::withMessages([
                'adults' => "Only {$seatsLeft} ".Str::plural('seat', $seatsLeft).' available on this '.($option['seat_key'] ? 'departure' : 'package').'. Please book for fewer travellers or contact us.',
            ]);
        }

        $booking = new Booking([
            'destination' => $option['destination'],
            'package_name' => $option['package_name'],
            'travel_date' => $travelDate,
            'return_date' => $option['days'] ? $travelDate->copy()->addDays($option['days'] - 1) : null,
            'adults' => $validated['adults'],
            'children' => $children,
            'amount' => $option['price'] ? $option['price'] * ($validated['adults'] + $children) : null,
            'status' => BookingStatus::Pending,
            'special_requests' => $validated['special_requests'] ?? null,
            'bookable_option' => $option['seat_key'],
        ]);
        $booking->client()->associate($request->user());
        $booking->bookable()->associate($bookable->package);
        $booking->save();

        return redirect()
            ->route('client.bookings.show', $booking->fresh()->reference)
            ->with('status', 'Thank you! Your booking request has been sent. Our team will confirm availability and the final price with you soon.');
    }
}
