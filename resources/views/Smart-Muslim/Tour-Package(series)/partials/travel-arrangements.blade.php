{{-- Flight passes and hotel list for a package's "Flights & stays" slide. Expects $details (outbound/return) and $hotels. --}}
@php
    $airport = static function (string $place): array {
        return preg_match('/^(.*?)\s*\(([A-Z]{3})\)\s*$/u', trim($place), $match) === 1
            ? ['code' => $match[2], 'city' => $match[1]]
            : ['code' => '', 'city' => trim($place)];
    };
    $flights = collect(['outbound' => 'Outbound', 'return' => 'Return'])
        ->filter(fn (string $label, string $key): bool => filled($details[$key]['flight'] ?? ''))
        ->map(function (string $label, string $key) use ($details, $airport): array {
            $flight = $details[$key];
            $places = preg_split('/\s*(?:→|->)\s*/u', (string) ($flight['route'] ?? ''), 2);
            $times = preg_split('/\s+[–-]\s+/u', (string) ($flight['time'] ?? ''), 2);

            return [
                'label' => $label,
                'flight' => $flight['flight'],
                'route' => (string) ($flight['route'] ?? ''),
                'from' => count($places) === 2 ? $airport($places[0]) : null,
                'to' => count($places) === 2 ? $airport($places[1]) : null,
                'departs' => count($times) === 2 ? $times[0] : (string) ($flight['time'] ?? ''),
                'arrives' => count($times) === 2 ? $times[1] : '',
                'duration' => (string) ($flight['duration'] ?? ''),
            ];
        });
@endphp

@if ($flights->isNotEmpty())
    <div class="series-details-columns series-flight-passes">
        @foreach ($flights as $flight)
            <article class="series-flight-pass series-flight-pass--{{ \Illuminate\Support\Str::lower($flight['label']) }}">
                <header>
                    <span class="series-flight-pass__label"><span aria-hidden="true">✈</span> {{ $flight['label'] }}</span>
                    <span class="series-flight-pass__number">{{ $flight['flight'] }}</span>
                </header>
                @if ($flight['from'] && $flight['to'])
                    <div class="series-flight-pass__route">
                        <div>
                            <strong>{{ $flight['from']['code'] ?: $flight['from']['city'] }}</strong>
                            @if ($flight['from']['code'] !== '')<small>{{ $flight['from']['city'] }}</small>@endif
                            @if ($flight['departs'] !== '')<time>{{ $flight['departs'] }}</time>@endif
                        </div>
                        <span class="series-flight-pass__path" aria-hidden="true"><span>✈</span></span>
                        <div>
                            <strong>{{ $flight['to']['code'] ?: $flight['to']['city'] }}</strong>
                            @if ($flight['to']['code'] !== '')<small>{{ $flight['to']['city'] }}</small>@endif
                            @if ($flight['arrives'] !== '')<time>{{ $flight['arrives'] }}</time>@endif
                        </div>
                    </div>
                    <p class="visually-hidden">{{ $flight['route'] }}</p>
                @else
                    <p>{{ $flight['route'] }}</p>
                    <p>{{ trim($flight['departs'].' – '.$flight['arrives'], ' –') }}</p>
                @endif
                @if ($flight['duration'] !== '')
                    <p class="series-flight-pass__duration"><span aria-hidden="true">⏱</span> {{ $flight['duration'] }}</p>
                @endif
            </article>
        @endforeach
    </div>
@endif

@if (! empty($hotels))
    <div class="series-details-hotels series-hotel-list">
        <strong>{{ $hotelsHeading ?? 'Hotels' }}</strong>
        @foreach ($hotels as $hotel)
            <article class="series-hotel">
                <span class="series-hotel__icon" aria-hidden="true">🏨</span>
                <div>
                    <h4>{{ $hotel['name'] ?? '' }}</h4>
                    @if (filled($hotel['room'] ?? '') || filled($hotel['location'] ?? ''))
                        <p class="series-hotel__meta">
                            @if (filled($hotel['room'] ?? ''))<span>{{ $hotel['room'] }}</span>@endif
                            @if (filled($hotel['location'] ?? ''))<span><span aria-hidden="true">📍</span> {{ $hotel['location'] }}</span>@endif
                        </p>
                    @endif
                    @if (filled($hotel['note'] ?? ''))
                        <p class="series-hotel__note">{{ $hotel['note'] }}</p>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@endif
