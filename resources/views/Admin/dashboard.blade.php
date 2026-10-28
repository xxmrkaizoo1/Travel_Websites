@extends('Admin.layouts.workspace')

@section('title', 'Overview')

@section('content')
    <section class="admin-welcome" id="overview">
        <div>
            <span class="admin-eyebrow">SMART HOLIDAYS · ADMIN</span>
            <h1>Travel overview</h1>
            <p>Here’s what’s happening with your travel business today.</p>
        </div>
        <a class="admin-primary-button" href="{{ route('admin.bookings.create') }}" data-admin-tour="new-booking"><span aria-hidden="true">＋</span> New booking</a>
    </section>

    <section class="admin-stats" aria-label="Business overview" data-admin-tour="stats">
        @foreach ($stats as $stat)
            <article class="admin-stat-card">
                <div class="admin-stat-top"><span>{{ $stat['label'] }}</span><span class="admin-stat-icon stat-{{ $stat['tone'] }}" aria-hidden="true">{{ $stat['icon'] }}</span></div>
                <strong class="admin-stat-value">{{ $stat['value'] }}</strong>
                <div class="admin-stat-foot">
                    @if ($stat['trend'] !== null)
                        <span class="admin-trend{{ $stat['trend'] < 0 ? ' is-down' : '' }}">{{ $stat['trend'] < 0 ? '↘' : '↗' }} {{ abs($stat['trend']) }}%</span><span>{{ $stat['foot'] }}</span>
                    @elseif ($stat['footTone'] === 'attention')
                        <span class="admin-pending">{{ $stat['foot'] }}</span>
                    @elseif ($stat['footTone'] === 'up')
                        <span class="admin-trend">{{ $stat['foot'] }}</span>
                    @else
                        <span>{{ $stat['foot'] }}</span>
                    @endif
                </div>
                <svg class="admin-sparkline spark-{{ $stat['tone'] }}" viewBox="0 0 100 34" preserveAspectRatio="none" aria-hidden="true"><path d="{{ $stat['sparkline'] }}"/></svg>
            </article>
        @endforeach
    </section>

    <section class="admin-dashboard-grid" id="reports">
        <article class="admin-panel admin-revenue-panel" data-admin-tour="revenue">
            <div class="admin-panel-heading">
                <div><h2>Revenue overview</h2><p>Confirmed and completed bookings, by booking date</p></div>
                <form class="admin-range-form" method="GET" action="{{ route('admin.dashboard') }}#reports">
                    <label class="visually-hidden" for="revenue-range">Revenue period</label>
                    <select class="admin-select-button" id="revenue-range" name="range" data-auto-submit>
                        @foreach ($ranges as $value => $label)
                            <option value="{{ $value }}" @selected($chart['range'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit">Show</button></noscript>
                </form>
            </div>
            <div class="admin-chart-summary">
                <strong>RM {{ number_format($chart['total']) }}</strong>
                @if ($chart['change'] !== null)
                    <span class="admin-trend{{ $chart['change'] < 0 ? ' is-down' : '' }}">{{ $chart['change'] < 0 ? '↘' : '↗' }} {{ abs($chart['change']) }}%</span>
                    <small>compared to {{ $chart['previousLabel'] }}</small>
                @else
                    <small>{{ $chart['rangeLabel'] }}</small>
                @endif
            </div>
            <div class="admin-chart" role="img" aria-label="Revenue for the {{ strtolower($chart['rangeLabel']) }}: RM {{ number_format($chart['total']) }} in total">
                <div class="admin-chart-y-labels">
                    @foreach ($chart['yLabels'] as $yLabel)
                        <span>{{ $yLabel }}</span>
                    @endforeach
                </div>
                <svg viewBox="0 0 700 205" preserveAspectRatio="none" aria-hidden="true">
                    <defs><linearGradient id="admin-chart-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#3477d3" stop-opacity=".2"/><stop offset="100%" stop-color="#3477d3" stop-opacity="0"/></linearGradient></defs>
                    <path class="admin-chart-gridline" d="M0 12H700M0 55H700M0 98H700M0 141H700M0 184H700"/>
                    <path class="admin-chart-area" d="{{ $chart['area'] }}"/>
                    <path class="admin-chart-line" d="{{ $chart['line'] }}"/>
                    <circle class="admin-chart-point" cx="{{ $chart['lastPoint']['x'] }}" cy="{{ $chart['lastPoint']['y'] }}" r="5"/>
                </svg>
                <div class="admin-chart-x-labels">
                    @foreach ($chart['xLabels'] as $xLabel)
                        <span>{{ $xLabel }}</span>
                    @endforeach
                </div>
            </div>
        </article>

        <article class="admin-panel admin-destinations-panel" id="destinations" data-admin-tour="destinations">
            <div class="admin-panel-heading">
                <div><h2>Popular destinations</h2><p>Bookings by destination</p></div>
                <a class="admin-view-link" href="{{ route('admin.destinations.index') }}" aria-label="View all destinations">•••</a>
            </div>
            <div class="admin-destination-list">
                @forelse ($popularDestinations as $destination)
                    <div class="admin-destination-row">
                        @if ($destination['image'])
                            <img src="{{ $destination['image'] }}" alt="" loading="lazy">
                        @else
                            <span class="admin-destination-tile" aria-hidden="true">{{ $destination['initials'] }}</span>
                        @endif
                        <span><strong>{{ $destination['name'] }}</strong><small>{{ number_format($destination['travellers']) }} {{ $destination['travellers'] === 1 ? 'traveller' : 'travellers' }} · RM {{ number_format($destination['revenue']) }}</small></span>
                        <strong class="admin-destination-total">{{ number_format($destination['bookings']) }}</strong>
                    </div>
                @empty
                    <p class="admin-panel-empty">Destinations appear here once you add bookings.</p>
                @endforelse
            </div>
            <a class="admin-panel-bottom-link" href="{{ route('admin.destinations.index') }}">View all destinations <span aria-hidden="true">→</span></a>
        </article>
    </section>

    <section class="admin-panel admin-loved-panel" id="package-likes" aria-labelledby="loved-packages-title" data-admin-tour="likes">
        <div class="admin-panel-heading">
            <div><h2 id="loved-packages-title">Most loved packages</h2><p>Smart Muslim packages visitors tapped ♥ on most</p></div>
            <a class="admin-view-link" href="{{ route('admin.package-likes') }}" aria-label="View all package likes">•••</a>
        </div>
        @if ($lovedPackages->isNotEmpty())
            <ol class="admin-loved-list">
                @foreach ($lovedPackages as $package)
                    <li>
                        <span class="admin-loved-rank" aria-hidden="true">{{ $loop->iteration }}</span>
                        <img src="{{ $package['image'] }}" alt="" loading="lazy">
                        <span class="admin-loved-name"><strong>{{ $package['name'] }}</strong><small>{{ $package['context'] }}</small></span>
                        <span class="admin-loved-total"><strong>♥ {{ number_format($package['total']) }}</strong>@if ($package['week'] > 0)<small>+{{ number_format($package['week']) }} this week</small>@endif</span>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="admin-panel-empty">No likes yet. Packages appear here once visitors tap ♥ on them.</p>
        @endif
        <a class="admin-panel-bottom-link" href="{{ route('admin.package-likes') }}">View like statistics <span aria-hidden="true">→</span></a>
    </section>

    <section class="admin-panel admin-bookings-panel" id="bookings" data-admin-tour="recent-bookings">
        <div class="admin-panel-heading admin-bookings-heading">
            <div><h2>Recent bookings</h2><p>Manage and track your latest client bookings</p></div>
            <div class="admin-bookings-heading-tools">
                <form class="admin-search" method="GET" action="{{ route('admin.bookings.index') }}" role="search">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <input type="search" name="search" placeholder="Search bookings..." aria-label="Search bookings">
                </form>
                <button class="admin-expand-button" type="button" data-booking-manager-open aria-haspopup="dialog" title="Open full screen to manage bookings">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                    <span>Full screen</span>
                </button>
            </div>
        </div>
        @include('Admin.bookings.partials.table', ['bookings' => $recentBookings, 'emptyMessage' => 'No bookings yet. Create the first one with “New booking”.'])
        <div class="admin-table-footer">
            <span>Showing <strong>{{ $recentBookings->count() }}</strong> of {{ number_format($bookingCount) }} bookings</span>
            <a href="{{ route('admin.bookings.index') }}">View all bookings <span aria-hidden="true">→</span></a>
        </div>
    </section>

    @include('Admin.bookings.partials.manager', ['bookings' => $managerBookings, 'total' => $bookingCount])

    <div data-admin-tour-root data-tour-name="{{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}" data-tour-offer="{{ session()->pull('admin_tour_offer', false) ? 'true' : 'false' }}" hidden></div>
    <button class="admin-help-button" type="button" data-admin-tour="help" data-admin-tour-start aria-label="Show me around the admin" title="Guided tour of the admin">
        <span aria-hidden="true">?</span>
    </button>
@endsection
