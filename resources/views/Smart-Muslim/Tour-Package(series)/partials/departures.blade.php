{{-- Departure dates with price and availability. Expects $departures; $bookingUrl adds a "Book a departure" button.
     With $seatsBooking (BookablePackage) and $seatsDestination, departures with a seat limit show the seats left. --}}
@if (! empty($departures))
    <div class="series-details-date-list series-departures">
        <strong>Available departures</strong>
        @foreach ($departures as $departure)
            @php
                $departureSeatsLeft = isset($seatsBooking, $seatsDestination)
                    ? $seatsBooking->seatsLeft(\App\Support\BookablePackage::departureKey($seatsDestination, (string) ($departure['date'] ?? '')))
                    : null;
            @endphp
            <article class="series-departure">
                <span class="series-departure__icon" aria-hidden="true">📅</span>
                <div>
                    <h4>{{ $departure['date'] ?? '' }}</h4>
                    <p>
                        @if (filled($departure['price'] ?? ''))<span class="series-departure__price">{{ $departure['price'] }}</span>@endif
                        @if ($departureSeatsLeft !== null)
                            <span @class(['series-departure__availability', 'series-departure__availability--full' => $departureSeatsLeft === 0])>{{ $departureSeatsLeft === 0 ? 'Fully booked' : $departureSeatsLeft.' '.\Illuminate\Support\Str::plural('seat', $departureSeatsLeft).' left' }}</span>
                        @elseif (filled($departure['availability'] ?? ''))<span class="series-departure__availability">{{ $departure['availability'] }}</span>@endif
                    </p>
                </div>
            </article>
        @endforeach
        @if (! empty($bookingUrl))
            <a class="package-book-now series-departures__book" href="{{ $bookingUrl }}">Book a departure <span aria-hidden="true">→</span></a>
        @endif
    </div>
@endif
