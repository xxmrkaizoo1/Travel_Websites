<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingRequest;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\User;
use App\Support\DestinationCatalog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
        ]);

        $bookings = Booking::query()
            ->with('client:id,name,email')
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('Admin.bookings.index', [
            'bookings' => $bookings,
            'filters' => $filters,
            'statusCounts' => Booking::query()->toBase()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create(Request $request, DestinationCatalog $catalog): View
    {
        $enquiry = $request->filled('enquiry')
            ? Enquiry::query()->whereNull('booking_id')->find($request->integer('enquiry'))
            : null;
        $client = $request->filled('client')
            ? User::query()->clients()->find($request->integer('client'))
            : $enquiry?->client ?? ($enquiry?->email ? User::query()->clients()->where('email', $enquiry->email)->first() : null);

        return view('Admin.bookings.form', [
            'booking' => new Booking([
                'user_id' => $client?->id,
                'destination' => $enquiry?->destination,
                'adults' => 1,
                'children' => 0,
                'status' => BookingStatus::Pending,
                'notes' => $enquiry ? "From enquiry: {$enquiry->message}" : null,
            ]),
            'enquiry' => $enquiry,
            'clients' => $this->clientOptions(),
            'destinations' => $catalog->names(),
        ]);
    }

    public function store(BookingRequest $request): RedirectResponse
    {
        $booking = DB::transaction(function () use ($request): Booking {
            $booking = Booking::query()->create($request->bookingData());

            if ($request->filled('enquiry_id')) {
                $enquiry = Enquiry::query()->lockForUpdate()->findOrFail($request->integer('enquiry_id'));
                $enquiry->booking()->associate($booking);
                $enquiry->client()->associate($enquiry->user_id ?? $booking->user_id);
                $enquiry->status = EnquiryStatus::Closed;
                $enquiry->save();
            }

            return $booking;
        });

        return redirect()->route('admin.bookings.index')->with('status', "Booking {$booking->reference} created.");
    }

    public function edit(Booking $booking, DestinationCatalog $catalog): View
    {
        return view('Admin.bookings.form', [
            'booking' => $booking->load('client', 'enquiry', 'bookable'),
            'enquiry' => null,
            'clients' => $this->clientOptions($booking->user_id),
            'destinations' => $catalog->names(),
        ]);
    }

    public function update(BookingRequest $request, Booking $booking): RedirectResponse
    {
        $booking->update($request->bookingData());

        return redirect()->route('admin.bookings.edit', $booking)->with('status', "Booking {$booking->reference} updated.");
    }

    /**
     * Quick status change from the full-screen bookings view on the dashboard. The same rules as the edit
     * page apply: a final amount before confirming, and enough seats on the package or departure.
     */
    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $status = BookingStatus::from($request->validate([
            'status' => ['required', Rule::enum(BookingStatus::class)],
        ])['status']);

        if ($status->earnsRevenue() && $booking->amount === null) {
            throw ValidationException::withMessages(['status' => 'Enter the final amount before confirming or completing. Open the booking to add it.']);
        }

        if (($problem = $booking->seatProblem($status, $booking->adults + $booking->children)) !== null) {
            throw ValidationException::withMessages(['status' => $problem]);
        }

        $booking->update(['status' => $status]);

        return response()->json([
            'status' => $status->value,
            'label' => $status->label(),
            'message' => "Booking {$booking->reference} is now {$status->label()}.",
        ]);
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('status', "Booking {$booking->reference} deleted.");
    }

    /**
     * Active clients for the client picker, plus the booking's current client even if suspended.
     *
     * @return Collection<int, User>
     */
    private function clientOptions(?int $currentClientId = null): Collection
    {
        return User::query()
            ->clients()
            ->where(fn ($query) => $query->whereNull('suspended_at')->when($currentClientId, fn ($query) => $query->orWhere('id', $currentClientId)))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);
    }
}
