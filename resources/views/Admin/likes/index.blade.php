@extends('Admin.layouts.workspace')

@section('title', 'Package likes')

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">MANAGEMENT</span>
            <h1>Package likes</h1>
            <p>How often visitors tap ♥ on your Smart Muslim packages. Use it to see which trips people are excited about, even before they book. Each browser counts once per package; signed-in clients count once per account.</p>
        </div>
    </section>

    <section class="admin-stats admin-like-stats" aria-label="Like statistics">
        @foreach ([
            ['Total likes', number_format($summary['total']), 'All time, across every package', '♥', 'rose'],
            ['Last 7 days', number_format($summary['week']), 'New likes this week', '↗', 'blue'],
            ['Packages loved', $summary['loved'].' / '.$summary['packages'], 'Have at least one like', '★', 'green'],
            ['From clients', $summary['memberShare'].'%', 'Likes from signed-in clients', '♧', 'amber'],
        ] as [$label, $value, $foot, $icon, $tone])
            <article class="admin-stat-card">
                <div class="admin-stat-top"><span>{{ $label }}</span><span class="admin-like-stat-icon like-tone-{{ $tone }}" aria-hidden="true">{{ $icon }}</span></div>
                <strong class="admin-stat-value">{{ $value }}</strong>
                <div class="admin-stat-foot"><span>{{ $foot }}</span></div>
            </article>
        @endforeach
    </section>

    <section class="admin-panel admin-like-trend" aria-labelledby="like-trend-title">
        <div class="admin-panel-heading">
            <div><h2 id="like-trend-title">Likes in the last 14 days</h2><p>New likes per day, across all packages</p></div>
        </div>
        <div class="admin-like-bars" role="img" aria-label="Likes per day for the last 14 days: {{ collect($daily)->map(fn ($day) => $day['date']->format('j M').' '.$day['likes'])->implode(', ') }}">
            @foreach ($daily as $day)
                <div class="admin-like-bar{{ $day['date']->isToday() ? ' is-today' : '' }}">
                    <span class="admin-like-bar-value">{{ $day['likes'] ?: '' }}</span>
                    <span class="admin-like-bar-fill" style="--like-bar: {{ round($day['likes'] / $dailyPeak * 100) }}%; --bar-index: {{ $loop->index }}"></span>
                    <small>{{ $day['date']->isToday() ? 'Today' : $day['date']->format('j M') }}</small>
                </div>
            @endforeach
        </div>
    </section>

    <section class="admin-panel admin-bookings-panel" aria-labelledby="like-table-title">
        <div class="admin-panel-heading admin-bookings-heading">
            <div><h2 id="like-table-title">Every package</h2><p>{{ $rows->count() }} {{ \Illuminate\Support\Str::plural('package', $rows->count()) }} · {{ $sorts[$sort] }}</p></div>
            <form class="admin-like-filters" method="GET" action="{{ route('admin.package-likes') }}">
                <label class="visually-hidden" for="like-group">Show</label>
                <select class="admin-select-button" id="like-group" name="group" data-auto-submit>
                    @foreach ($groups as $value => $label)
                        <option value="{{ $value }}" @selected($group === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="visually-hidden" for="like-sort">Sort by</label>
                <select class="admin-select-button" id="like-sort" name="sort" data-auto-submit>
                    @foreach ($sorts as $value => $label)
                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit">Show</button></noscript>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-bookings-table admin-like-table">
                <thead>
                    <tr><th>#</th><th>Package</th><th>Total likes</th><th>Last 7 days</th><th>Last 30 days</th><th>From clients</th><th>Last liked</th><th><span class="visually-hidden">Links</span></th></tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td class="admin-like-rank">{{ $loop->iteration }}</td>
                            <td>
                                <span class="admin-like-package">
                                    <img src="{{ $row['image'] }}" alt="" loading="lazy">
                                    <span><strong>{{ $row['name'] }}</strong><small class="admin-cell-note">{{ $row['context'] }}</small></span>
                                </span>
                            </td>
                            <td><span @class(['admin-like-count', 'is-zero' => $row['total'] === 0])>♥ {{ number_format($row['total']) }}</span></td>
                            <td>{{ $row['week'] > 0 ? '+'.number_format($row['week']) : '–' }}</td>
                            <td>{{ $row['month'] > 0 ? '+'.number_format($row['month']) : '–' }}</td>
                            <td>{{ number_format($row['members']) }}</td>
                            <td>
                                @if ($row['last_liked_at'])
                                    <time datetime="{{ $row['last_liked_at']->toIso8601String() }}" title="{{ $row['last_liked_at']->format('j M Y, g:i a') }}">{{ $row['last_liked_at']->diffForHumans() }}</time>
                                @else
                                    <span class="admin-cell-note">Not yet</span>
                                @endif
                            </td>
                            <td><a class="admin-row-link" href="{{ $row['url'] }}" target="_blank" rel="noreferrer">View ↗</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="admin-table-empty">No packages in this group yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
