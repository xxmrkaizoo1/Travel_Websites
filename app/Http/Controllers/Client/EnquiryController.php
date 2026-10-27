<?php

namespace App\Http\Controllers\Client;

use App\Enums\EnquirySource;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Support\DestinationCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Trip requests a client sends from their account. They land in the admin enquiry inbox.
 */
class EnquiryController extends Controller
{
    public function index(Request $request, DestinationCatalog $catalog): View
    {
        $client = $request->user();
        $aboutBooking = $request->filled('booking')
            ? $client->bookings()->where('reference', $request->string('booking'))->first()
            : null;

        return view('client.portal.enquiries.index', [
            'enquiries' => $client->enquiries()->with('booking:id,reference')->latest()->latest('id')->paginate(10),
            'destinations' => $catalog->names(),
            'aboutBooking' => $aboutBooking,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destination' => ['required', 'string', 'max:120'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'adults' => ['required', 'integer', 'min:1', 'max:200'],
            'children' => ['nullable', 'integer', 'min:0', 'max:200'],
            'message' => ['required', 'string', 'max:1500'],
        ], [
            'preferred_date.after_or_equal' => 'Choose a travel date from today onwards.',
        ]);

        $client = $request->user();
        $details = array_filter([
            filled($validated['preferred_date'] ?? null) ? 'Preferred travel date: '.$validated['preferred_date'] : null,
            'Travellers: '.$validated['adults'].' adult(s)'.((int) ($validated['children'] ?? 0) > 0 ? ', '.$validated['children'].' child(ren)' : ''),
        ]);

        $enquiry = new Enquiry([
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'destination' => $validated['destination'],
            'message' => implode("\n", $details)."\n\n".$validated['message'],
            'source' => EnquirySource::Website,
        ]);
        $enquiry->client()->associate($client);
        $enquiry->save();

        return redirect()->route('client.enquiries.index')->with('status', 'Thank you! Your trip request has been sent. Our team will contact you soon.');
    }
}
