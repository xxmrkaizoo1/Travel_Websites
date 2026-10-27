<?php

namespace App\Http\Controllers\Client;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Support\DestinationCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DestinationCatalog $catalog): View
    {
        $client = $request->user();
        $upcoming = $client->bookings()->upcoming()->orderBy('travel_date')->get();
        $nextTrip = $upcoming->first();
        $activeTrips = $client->bookings()->where('status', '!=', BookingStatus::Cancelled->value)->get(['adults', 'children']);

        return view('client.portal.dashboard', [
            'client' => $client,
            'nextTrip' => $nextTrip,
            'nextTripImage' => $nextTrip ? $catalog->imageFor($nextTrip->destination) : null,
            'upcoming' => $upcoming->skip(1)->take(3),
            'stats' => [
                'upcoming' => $upcoming->count(),
                'trips' => $activeTrips->count(),
                'openRequests' => $client->enquiries()->pending()->count(),
                'travellers' => $activeTrips->sum(fn ($booking): int => $booking->adults + $booking->children),
            ],
            'requests' => $client->enquiries()->with('booking:id,reference')->latest()->latest('id')->limit(3)->get(),
        ]);
    }
}
