{{-- One itinerary day on a timeline, with meals and transfer as tags. Expects $day. --}}
<article class="series-itinerary-day">
    <span class="series-itinerary-day__badge">{{ $day['day'] }}</span>
    <div>
        <h4>{{ $day['title'] }}</h4>
        <p>{{ $day['places'] }}</p>
        @if (filled($day['meals'] ?? '') || filled($day['transfer'] ?? ''))
            <p class="series-itinerary-day__tags">
                @if (filled($day['meals'] ?? ''))<span><span aria-hidden="true">🍽</span> Meals: {{ $day['meals'] }}</span>@endif
                @if (filled($day['transfer'] ?? ''))<span><span aria-hidden="true">🚌</span> Transfer: {{ $day['transfer'] }}</span>@endif
            </p>
        @endif
    </div>
</article>
