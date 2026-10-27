{{-- Travel essentials and a seasonal weather guide. Expects $essentials and $weather. --}}
@php
    $essentialFields = [
        'visa' => ['Visa', '🛂'],
        'arrival_card' => ['Arrival card', '📝'],
        'currency' => ['Currency', '💱'],
        'time' => ['Time zone', '🕘'],
        'language' => ['Language', '🗣'],
    ];
    $seasonIcons = ['spring' => '🌸', 'summer' => '☀️', 'autumn' => '🍁', 'fall' => '🍁', 'winter' => '❄️', 'dry' => '☀️', 'wet' => '🌧', 'rainy' => '🌧', 'monsoon' => '🌧'];
    $filledEssentials = array_filter($essentialFields, fn (array $field, string $key): bool => filled($essentials[$key] ?? ''), ARRAY_FILTER_USE_BOTH);
@endphp

@if ($filledEssentials !== [])
    <div class="series-details-highlights series-essentials">
        @foreach ($filledEssentials as $key => [$label, $icon])
            <article>
                <span><span class="series-essentials__icon" aria-hidden="true">{{ $icon }}</span> {{ $label }}</span>
                <strong>{{ $essentials[$key] }}</strong>
            </article>
        @endforeach
    </div>
@endif

@if (! empty($weather))
    <div class="series-weather series-seasons">
        <strong>Seasonal guide</strong>
        @foreach ($weather as $season)
            @php
                [$seasonName, $seasonMonths] = array_pad(preg_split('/\s*·\s*/u', (string) ($season['season'] ?? ''), 2), 2, '');
                $seasonIcon = collect($seasonIcons)->first(fn (string $icon, string $word): bool => str_contains(\Illuminate\Support\Str::lower($seasonName), $word)) ?? '🌤';
                $description = (string) ($season['description'] ?? '');
            @endphp
            <article class="series-season">
                <span class="series-season__icon" aria-hidden="true">{{ $seasonIcon }}</span>
                <h4>{{ $seasonName }}</h4>
                @if ($seasonMonths !== '')<small>{{ $seasonMonths }}</small>@endif
                @if (filled($season['temperature'] ?? ''))<p class="series-season__temperature">{{ $season['temperature'] }}</p>@endif
                @if ($description !== '' && \Illuminate\Support\Str::lower($description) !== \Illuminate\Support\Str::lower($seasonName))
                    <p>{{ $description }}</p>
                @endif
            </article>
        @endforeach
    </div>
@endif
