{{-- Departure dates with price and availability. Expects $departures; $bookingUrl adds a "Book a departure" button. --}}
@if (! empty($departures))
    <div class="series-details-date-list series-departures">
        <strong>Available departures</strong>
        @foreach ($departures as $departure)
            <article class="series-departure">
                <span class="series-departure__icon" aria-hidden="true">📅</span>
                <div>
                    <h4>{{ $departure['date'] ?? '' }}</h4>
                    <p>
                        @if (filled($departure['price'] ?? ''))<span class="series-departure__price">{{ $departure['price'] }}</span>@endif
                        @if (filled($departure['availability'] ?? ''))<span class="series-departure__availability">{{ $departure['availability'] }}</span>@endif
                    </p>
                </div>
            </article>
        @endforeach
        @if (! empty($bookingUrl))
            <a class="package-book-now series-departures__book" href="{{ $bookingUrl }}">Book a departure <span aria-hidden="true">→</span></a>
        @endif
    </div>
@endif
