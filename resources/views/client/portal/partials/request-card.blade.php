{{-- A trip request (enquiry) the client sent. Expects $tripRequest with its booking loaded. --}}
<article class="cp-request">
    <div>
        <strong>{{ $tripRequest->destination ?: 'General enquiry' }}</strong>
        <small>Sent {{ $tripRequest->created_at->format('d M Y') }}</small>
        <p>{{ \Illuminate\Support\Str::limit($tripRequest->message, 160) }}</p>
        @if ($tripRequest->booking)
            <a href="{{ route('client.bookings.show', $tripRequest->booking->reference) }}">Booked as {{ $tripRequest->booking->reference }} →</a>
        @endif
    </div>
    <span class="cp-status is-request-{{ $tripRequest->status->value }}">{{ $tripRequest->status->clientLabel($tripRequest->booking_id !== null) }}</span>
</article>
