<?php

namespace App\Http\Controllers\Client;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\DestinationCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    private const TABS = ['upcoming', 'past', 'cancelled', 'all'];

    public function index(Request $request): View
    {
        $tab = $request->validate(['tab' => ['nullable', Rule::in(self::TABS)]])['tab'] ?? 'upcoming';
        $bookings = $request->user()->bookings();

        $bookings = match ($tab) {
            'upcoming' => $bookings->upcoming()->orderBy('travel_date'),
            'past' => $bookings->whereDate('travel_date', '<', today())->where('status', '!=', BookingStatus::Cancelled->value)->latest('travel_date'),
            'cancelled' => $bookings->where('status', BookingStatus::Cancelled->value)->latest('travel_date'),
            'all' => $bookings->latest('travel_date'),
        };

        return view('client.portal.bookings.index', [
            'bookings' => $bookings->paginate(10)->withQueryString(),
            'tab' => $tab,
        ]);
    }

    public function show(Booking $clientBooking, DestinationCatalog $catalog): View
    {
        return view('client.portal.bookings.show', [
            'booking' => $clientBooking,
            'image' => $catalog->imageFor($clientBooking->destination),
        ]);
    }
}
