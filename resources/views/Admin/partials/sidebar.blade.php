<aside class="admin-sidebar" id="admin-sidebar" aria-label="Admin navigation">
    <a class="admin-brand" href="{{ route('home') }}">
        <span class="admin-brand-mark" aria-hidden="true">
            <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
        </span>
        <span><strong>SMART</strong><small>HOLIDAYS ADMIN</small></span>
    </a>

    <div class="admin-sidebar-body">
    <span class="admin-nav-label">WORKSPACE</span>
    <nav class="admin-navigation">
        @foreach ([
            ['admin.dashboard', 'admin.dashboard', '⌂', 'Overview', null],
            ['admin.bookings.index', 'admin.bookings.*', '▤', 'Bookings', null],
            ['admin.enquiries.index', 'admin.enquiries.*', '✉', 'Enquiries', $sidebarPendingEnquiries ?? 0],
            ['admin.destinations.index', 'admin.destinations.*', '◎', 'Destinations', null],
            ['admin.online-booking.edit', 'admin.online-booking.*', '☑', 'Online booking', null],
        ] as [$routeName, $activePattern, $icon, $label, $badge])
            <a class="admin-nav-link{{ request()->routeIs($activePattern) ? ' is-active' : '' }}" href="{{ route($routeName) }}" @if (request()->routeIs($activePattern)) aria-current="page" @endif>
                <span class="admin-nav-icon" aria-hidden="true">{{ $icon }}</span> {{ $label }}
                @if ($badge)
                    <span class="admin-nav-count" aria-label="{{ $badge }} pending">{{ $badge }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <span class="admin-nav-label admin-tools-label">MANAGEMENT</span>
    <nav class="admin-navigation">
        <a class="admin-nav-link{{ request()->routeIs('admin.clients.*') ? ' is-active' : '' }}" href="{{ route('admin.clients.index') }}" @if (request()->routeIs('admin.clients.*')) aria-current="page" @endif>
            <span class="admin-nav-icon" aria-hidden="true">♧</span> Clients
        </a>
        <a class="admin-nav-link{{ request()->routeIs('admin.package-likes') ? ' is-active' : '' }}" href="{{ route('admin.package-likes') }}" @if (request()->routeIs('admin.package-likes')) aria-current="page" @endif>
            <span class="admin-nav-icon" aria-hidden="true">♡</span> Package likes
        </a>
        <span class="admin-nav-link admin-nav-link-disabled" aria-disabled="true">
            <span class="admin-nav-icon" aria-hidden="true">♙</span> Team <small>Coming soon</small>
        </span>
        <a class="admin-nav-link{{ request()->routeIs('admin.settings*') ? ' is-active' : '' }}" href="{{ route('admin.settings') }}" @if (request()->routeIs('admin.settings*')) aria-current="page" @endif>
            <span class="admin-nav-icon" aria-hidden="true">⚙</span> Settings
        </a>
    </nav>

    <span class="admin-nav-label admin-tools-label">SMART HOLIDAYS</span>
    <nav class="admin-navigation" aria-label="Smart Holidays">
        <details class="admin-nav-group" open>
            <summary class="admin-nav-link admin-nav-parent" data-context-menu="tour-package-menu" data-page-name="Smart Holidays" data-edit-url="{{ route('admin.smart-holidays.edit') }}" data-view-url="{{ route('home') }}" data-context-disable-create="true" data-edit-label="Edit homepage" data-view-label="View website" title="Right-click for Smart Holidays actions">
                <span class="admin-nav-icon" aria-hidden="true">✈</span> Smart Holidays
                <span class="admin-nav-chevron" aria-hidden="true">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 7.5 5 5 5-5"/></svg>
                </span>
            </summary>
            <nav class="admin-subnav" aria-label="Smart Holidays pages">
                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Smart Holidays homepage" data-edit-url="{{ route('admin.smart-holidays.edit') }}" data-view-url="{{ route('home') }}" data-context-disable-create="true" data-edit-label="Edit homepage" data-view-label="View homepage" title="Right-click for homepage actions" href="{{ route('admin.smart-holidays.edit') }}">Homepage</a>
                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Group Tours" data-edit-url="{{ route('admin.group-tours.edit') }}" data-view-url="{{ route('group-tours') }}" data-context-disable-create="true" data-edit-label="Edit Group Tours" data-view-label="View Group Tours" title="Right-click for Group Tours actions" href="{{ route('admin.group-tours.edit') }}">Group Tours</a>
                <a class="admin-nav-link admin-subnav-link admin-subnav-child" style="--nest: 1" data-context-menu="tour-package-menu" data-page-name="Tour Destinations" data-edit-url="{{ route('admin.group-tours.edit') }}" data-view-url="{{ route('group-tours') }}" data-context-disable-create="true" data-edit-label="Edit tour destinations" data-view-label="View tour destinations" title="Right-click for tour destination actions" href="{{ route('admin.group-tours.edit') }}"><span class="admin-subnav-name">Tour Destinations</span> <small>Page</small></a>
                @foreach ($groupTourDestinations as $sidebarGroupDestination)
                    <a class="admin-nav-link admin-subnav-link admin-subnav-child" style="--nest: 2" data-context-menu="tour-package-menu" data-page-name="{{ $sidebarGroupDestination->name }}" data-edit-url="{{ route('admin.group-tour-destinations.edit', $sidebarGroupDestination) }}" data-view-url="{{ route('group-tours.destination', $sidebarGroupDestination) }}" data-context-create-url="{{ route('admin.group-tour-destinations.create') }}" data-context-create-title="Create destination page" data-context-create-label="New destination page" data-edit-label="Edit {{ $sidebarGroupDestination->name }}" data-view-label="View {{ $sidebarGroupDestination->name }}" title="Right-click for {{ $sidebarGroupDestination->name }} actions" href="{{ route('admin.group-tour-destinations.edit', $sidebarGroupDestination) }}"><span class="admin-subnav-name">{{ $sidebarGroupDestination->name }}</span> <small>{{ count($sidebarGroupDestination->states ?? []) }} {{ $sidebarGroupDestination->placeTerm(count($sidebarGroupDestination->states ?? [])) }}</small></a>
                @endforeach
                <a class="admin-nav-link admin-subnav-link admin-subnav-child admin-subnav-new" style="--nest: 2" href="{{ route('admin.group-tour-destinations.create') }}">＋ New destination page</a>
                <a class="admin-nav-link admin-subnav-link admin-subnav-child" style="--nest: 1" data-context-menu="tour-package-menu" data-page-name="Booking Conditions" data-edit-url="{{ route('admin.group-tours.booking-conditions.edit') }}" data-view-url="{{ route('group-tours.booking-conditions') }}" data-context-disable-create="true" data-edit-label="Edit booking conditions" data-view-label="View booking conditions" title="Right-click for booking condition actions" href="{{ route('admin.group-tours.booking-conditions.edit') }}"><span class="admin-subnav-name">Booking Conditions</span> <small>Page</small></a>
                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Ground Tours" data-edit-url="{{ route('admin.ground-tours.edit') }}" data-view-url="{{ route('ground-tours') }}" data-context-disable-create="true" data-edit-label="Edit Ground Tours" data-view-label="View Ground Tours" title="Right-click for Ground Tours actions" href="{{ route('admin.ground-tours.edit') }}">Ground Tours</a>
                <a class="admin-nav-link admin-subnav-link admin-subnav-child" style="--nest: 1" data-context-menu="tour-package-menu" data-page-name="Ground Destinations" data-edit-url="{{ route('admin.ground-tours.edit') }}" data-view-url="{{ route('ground-tours') }}" data-context-disable-create="true" data-edit-label="Edit ground destinations" data-view-label="View ground destinations" title="Right-click for ground destination actions" href="{{ route('admin.ground-tours.edit') }}"><span class="admin-subnav-name">Ground Destinations</span> <small>Page</small></a>
                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Domestic Tours" data-edit-url="{{ route('admin.domestic-tours.edit') }}" data-view-url="{{ route('domestic-tours') }}" data-context-disable-create="true" data-edit-label="Edit Domestic Tours" data-view-label="View Domestic Tours" title="Right-click for Domestic Tours actions" href="{{ route('admin.domestic-tours.edit') }}">Domestic Tours</a>
                <a class="admin-nav-link admin-subnav-link admin-subnav-child" style="--nest: 1" data-context-menu="tour-package-menu" data-page-name="Malaysia" data-edit-url="{{ route('admin.domestic-tours.edit') }}" data-view-url="{{ route('domestic-tours') }}" data-context-disable-create="true" data-edit-label="Edit Malaysia domestic tours" data-view-label="View Malaysia domestic tours" title="Right-click for Malaysia domestic tour actions" href="{{ route('admin.domestic-tours.edit') }}"><span class="admin-subnav-name">Malaysia</span> <small>Page</small></a>
                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Ticketing" data-edit-url="{{ route('admin.ticketing.edit') }}" data-view-url="{{ route('ticketing') }}" data-context-disable-create="true" data-edit-label="Edit Ticketing" data-view-label="View Ticketing" title="Right-click for Ticketing actions" href="{{ route('admin.ticketing.edit') }}">Ticketing</a>
                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Contact Us" data-edit-url="{{ route('admin.contact.edit') }}" data-view-url="{{ route('contact') }}" data-context-disable-create="true" data-edit-label="Edit Contact Us" data-view-label="View Contact Us" title="Right-click for Contact Us actions" href="{{ route('admin.contact.edit') }}">Contact Us</a>
            </nav>
        </details>
    </nav>

    <span class="admin-nav-label admin-tools-label">SMART MUSLIM TOUR</span>
    <nav class="admin-navigation" aria-label="Smart Muslim Tour">
        <details class="admin-nav-group">
            <summary class="admin-nav-link admin-nav-parent" data-context-menu="tour-package-menu" data-page-name="Smart Muslim Homepage content" data-edit-url="{{ route('admin.homepage') }}" data-view-url="{{ route('smart-muslim') }}" data-edit-label="Manage homepage content" data-view-label="View homepage" data-context-create-url="{{ route('admin.homepage.create') }}" data-context-create-title="Create homepage campaign" data-context-create-label="New campaign" title="Right-click for homepage actions">
                <span class="admin-nav-icon" aria-hidden="true">▧</span> Smart Muslim Homepage content
                <span class="admin-nav-chevron" aria-hidden="true">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 7.5 5 5 5-5"/></svg>
                </span>
            </summary>
            <nav class="admin-subnav" aria-label="Smart Muslim Homepage content">
                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Homepage campaigns" data-edit-url="{{ route('admin.homepage') }}" data-view-url="{{ route('smart-muslim') }}" data-edit-label="Manage campaigns" data-view-label="View homepage" data-context-create-url="{{ route('admin.homepage.create') }}" data-context-create-title="Create homepage campaign" data-context-create-label="New campaign" title="Right-click for campaign actions" href="{{ route('admin.homepage') }}">Campaigns</a>
                @foreach ($sidebarHomepageCampaigns as $sidebarCampaign)
                    <a class="admin-nav-link admin-subnav-link admin-subnav-child" style="--nest: 1" data-context-menu="tour-package-menu" data-page-name="{{ $sidebarCampaign->headline }}" data-edit-url="{{ route('admin.homepage.edit', $sidebarCampaign) }}" data-view-url="{{ route('smart-muslim') }}" data-delete-url="{{ route('admin.homepage.destroy', $sidebarCampaign) }}" data-edit-label="Edit campaign" data-view-label="View homepage" data-delete-label="Delete campaign" data-delete-confirm="Delete this homepage campaign? This cannot be undone." data-context-create-url="{{ route('admin.homepage.create') }}" data-context-create-title="Create homepage campaign" data-context-create-label="New campaign" title="Right-click for campaign actions" href="{{ route('admin.homepage.edit', $sidebarCampaign) }}"><span class="admin-subnav-name">{{ $sidebarCampaign->headline }}</span> <small>Campaign</small></a>
                @endforeach
                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Homepage services" data-edit-url="{{ route('admin.homepage.services') }}" data-view-url="{{ route('smart-muslim') }}" data-context-disable-create="true" data-edit-label="Manage services" data-view-label="View homepage" title="Right-click for service actions" href="{{ route('admin.homepage.services') }}">Services</a>
                @foreach ($sidebarHomepageServices as $sidebarService)
                    <a class="admin-nav-link admin-subnav-link admin-subnav-child" style="--nest: 1" data-context-menu="tour-package-menu" data-page-name="{{ $sidebarService->title }}" data-edit-url="{{ route('admin.homepage.services.edit', $sidebarService) }}" data-view-url="{{ route('smart-muslim') }}" data-context-disable-create="true" data-edit-label="Edit service" data-view-label="View homepage" title="Right-click for service actions" href="{{ route('admin.homepage.services.edit', $sidebarService) }}"><span class="admin-subnav-name">{{ $sidebarService->title }}</span> <small>Service</small></a>
                @endforeach
                <a class="admin-nav-link admin-subnav-link admin-subnav-new" data-context-menu="tour-package-menu" data-page-name="Smart Muslim Homepage content" data-context-create-url="{{ route('admin.homepage.create') }}" data-context-create-title="Create homepage campaign" data-context-create-label="New campaign" title="Right-click for campaign actions" href="{{ route('admin.homepage.create') }}">＋ New campaign</a>
            </nav>
        </details>
        <details class="admin-nav-group" open>
            <summary class="admin-nav-link admin-nav-parent" data-context-menu="tour-package-menu" data-navigation-parent-target="" data-navigation-parent-name="Tour Package (Private)" title="Right-click to create a new page">
                <span class="admin-nav-icon" aria-hidden="true">◇</span> Tour Package (Private)
                <span class="admin-nav-chevron" aria-hidden="true">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 7.5 5 5 5-5"/></svg>
                </span>
            </summary>
            <nav class="admin-subnav" aria-label="Tour Package (Private)">
<div class="admin-subnav-entry">
    <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Private tour introduction" data-edit-url="{{ route('admin.private-tours.edit') }}" data-edit-label="Edit introduction" data-view-url="{{ route('tour-package-private') }}" data-view-label="View introduction" data-context-disable-create="true" title="Right-click for introduction actions" href="{{ route('admin.private-tours.edit') }}"><span class="admin-subnav-name">Introduction</span> <small>Intro</small></a>
    <span class="admin-nav-move-spacer" aria-hidden="true"></span>
</div>
<div class="admin-subnav-entry">
    <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Island &amp; Honeymoon" data-page-slug="island-honeymoon" data-navigation-parent-target="island-honeymoon" data-navigation-parent-name="Island &amp; Honeymoon" data-edit-url="{{ route('admin.island-honeymoon') }}" data-view-url="{{ route('island-honeymoon') }}" href="{{ route('admin.island-honeymoon') }}"><span class="admin-subnav-name">Island &amp; Honeymoon</span> <small>Intro</small></a>
    <span class="admin-nav-move-spacer" aria-hidden="true"></span>
</div>
                @foreach ($sidebarIslandDestinations as $sidebarDestination)
                    <a class="admin-nav-link admin-subnav-link admin-subnav-child" style="--nest: 1" data-context-menu="tour-package-menu" data-page-name="{{ $sidebarDestination->name }}" data-edit-url="{{ route('admin.island-honeymoon.destinations.edit', $sidebarDestination) }}" data-view-url="{{ route('island-honeymoon') }}" data-delete-url="{{ route('admin.island-honeymoon.destinations.destroy', $sidebarDestination) }}" data-edit-label="Edit spotlight" data-view-label="View Island &amp; Honeymoon" data-delete-label="Delete spotlight" data-delete-confirm="Delete this destination spotlight? This cannot be undone." data-context-create-url="{{ route('admin.island-honeymoon.destinations.create') }}" data-context-create-title="Create destination spotlight" data-context-create-label="New spotlight" title="Right-click for spotlight actions" href="{{ route('admin.island-honeymoon.destinations.edit', $sidebarDestination) }}"><span class="admin-subnav-name">{{ $sidebarDestination->name }}</span> <small>Spotlight</small></a>
                @endforeach
                @include('Admin.partials.tour-page-children', ['parentSlug' => 'island-honeymoon', 'depth' => 1])
<div class="admin-subnav-entry">
    <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Indonesia" data-page-slug="indonesia" data-navigation-parent-target="indonesia" data-navigation-parent-name="Indonesia" data-edit-url="{{ route('admin.tour-packages.edit', 'indonesia') }}" data-view-url="{{ route('indonesia') }}" href="{{ route('admin.tour-packages.edit', 'indonesia') }}"><span class="admin-subnav-name">Indonesia</span> <small>Intro</small></a>
    <span class="admin-nav-move-spacer" aria-hidden="true"></span>
</div>
                @include('Admin.partials.tour-page-children', ['parentSlug' => 'indonesia', 'depth' => 1])
<div class="admin-subnav-entry">
    <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Thailand" data-page-slug="thailand" data-navigation-parent-target="thailand" data-navigation-parent-name="Thailand" data-edit-url="{{ route('admin.tour-packages.edit', 'thailand') }}" data-view-url="{{ route('thailand') }}" href="{{ route('admin.tour-packages.edit', 'thailand') }}"><span class="admin-subnav-name">Thailand</span> <small>Intro</small></a>
    <span class="admin-nav-move-spacer" aria-hidden="true"></span>
</div>
                @include('Admin.partials.tour-page-children', ['parentSlug' => 'thailand', 'depth' => 1])
<div class="admin-subnav-entry">
    <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Vietnam" data-page-slug="vietnam" data-navigation-parent-target="vietnam" data-navigation-parent-name="Vietnam" data-edit-url="{{ route('admin.tour-packages.edit', 'vietnam') }}" data-view-url="{{ route('vietnam') }}" href="{{ route('admin.tour-packages.edit', 'vietnam') }}"><span class="admin-subnav-name">Vietnam</span> <small>Intro</small></a>
    <span class="admin-nav-move-spacer" aria-hidden="true"></span>
</div>
                @include('Admin.partials.tour-page-children', ['parentSlug' => 'vietnam', 'depth' => 1])
<div class="admin-subnav-entry">
    <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Philippines" data-page-slug="philippines" data-navigation-parent-target="philippines" data-navigation-parent-name="Philippines" data-edit-url="{{ route('admin.tour-packages.edit', 'philippines') }}" data-view-url="{{ route('philippines') }}" href="{{ route('admin.tour-packages.edit', 'philippines') }}"><span class="admin-subnav-name">Philippines</span> <small>Intro</small></a>
    <span class="admin-nav-move-spacer" aria-hidden="true"></span>
</div>
                @include('Admin.partials.tour-page-children', ['parentSlug' => 'philippines', 'depth' => 1])
                @include('Admin.partials.tour-page-children', ['parentSlug' => '', 'depth' => 0])
                                                                <a class="admin-nav-link admin-subnav-link admin-subnav-new" data-context-menu="tour-package-menu" data-navigation-parent-target="" data-navigation-parent-name="Tour Package (Private)" title="Right-click for page actions" href="{{ route('admin.tour-packages.create') }}">＋ New page</a>
                                                            </nav>
                                                        </details>
        <details class="admin-nav-group" open>
            <summary class="admin-nav-link admin-nav-parent" data-context-menu="tour-package-menu" data-navigation-parent-target="series-departures-2026" data-navigation-parent-name="Tour Package (Series)" title="Right-click to create a new page">
                <span class="admin-nav-icon" aria-hidden="true">▤</span> Tour Package (Series)
                <span class="admin-nav-chevron" aria-hidden="true">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 7.5 5 5 5-5"/></svg>
                </span>
            </summary>
            <nav class="admin-subnav" aria-label="Tour Package (Series)">
                @php($seriesIntroductionName = $customTourPages->firstWhere('slug', 'series-departures-2026')?->name ?? 'Series Departures 2026')
<div class="admin-subnav-entry">
    <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="{{ $seriesIntroductionName }}" data-page-slug="series-departures-2026" data-navigation-parent-target="series-departures-2026" data-navigation-parent-name="Tour Package (Series)" data-edit-url="{{ route('admin.tour-packages.edit', 'series-departures-2026') }}" data-view-url="{{ route('tour-package.show', 'series-departures-2026') }}" href="{{ route('admin.tour-packages.edit', 'series-departures-2026') }}"><span class="admin-subnav-name">{{ $seriesIntroductionName }}</span> <small>Intro</small></a>
    <button class="admin-nav-move-button" type="button" data-navigation-select="series-departures-2026" aria-label="Page actions for {{ $seriesIntroductionName }}" title="Page actions">↗</button>
</div>
                @include('Admin.partials.tour-page-children', ['parentSlug' => 'series-departures-2026', 'depth' => 0, 'directSeriesEntries' => true])
                <a class="admin-nav-link admin-subnav-link admin-subnav-new" data-context-menu="tour-package-menu" data-page-slug="series-departures-2026" data-page-name="Series Departures 2026" data-navigation-parent-target="series-departures-2026" data-navigation-parent-name="Tour Package (Series)" title="Right-click for page actions" href="{{ route('admin.tour-packages.create', ['parent' => 'series-departures-2026']) }}">＋ New page</a>
            </nav>
        </details>
        <details class="admin-nav-group">
            <summary class="admin-nav-link admin-nav-parent" data-context-menu="tour-package-menu" data-page-name="China Muslim" data-edit-url="{{ route('admin.china-muslim') }}" data-view-url="{{ route('china-muslim') }}" data-edit-label="Manage China Muslim" data-view-label="View China Muslim" data-context-create-url="{{ route('admin.china-muslim.create') }}" data-context-create-title="Create China Muslim package" data-context-create-label="New package" title="Right-click for China Muslim actions">
                <span class="admin-nav-icon" aria-hidden="true">◎</span> China Muslim
                <span class="admin-nav-chevron" aria-hidden="true">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 7.5 5 5 5-5"/></svg>
                </span>
            </summary>
            <nav class="admin-subnav" aria-label="China Muslim">
                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="China Muslim overview" data-edit-url="{{ route('admin.china-muslim') }}" data-view-url="{{ route('china-muslim') }}" data-edit-label="Manage China Muslim" data-view-label="View China Muslim" data-context-create-url="{{ route('admin.china-muslim.create') }}" data-context-create-title="Create China Muslim package" data-context-create-label="New package" title="Right-click for China Muslim actions" href="{{ route('admin.china-muslim') }}">Overview &amp; about</a>
                @foreach ($sidebarChinaMuslimPackages as $sidebarPackage)
                    <a class="admin-nav-link admin-subnav-link admin-subnav-child" style="--nest: 1" data-context-menu="tour-package-menu" data-page-name="{{ $sidebarPackage->title }}" data-edit-url="{{ route('admin.china-muslim.edit', $sidebarPackage) }}" data-view-url="{{ route('china-muslim') }}" data-delete-url="{{ route('admin.china-muslim.destroy', $sidebarPackage) }}" data-edit-label="Edit package" data-view-label="View China Muslim" data-delete-label="Delete package" data-delete-confirm="Delete this China Muslim package? This cannot be undone." data-context-create-url="{{ route('admin.china-muslim.create') }}" data-context-create-title="Create China Muslim package" data-context-create-label="New package" title="Right-click for package actions" href="{{ route('admin.china-muslim.edit', $sidebarPackage) }}"><span class="admin-subnav-name">{{ $sidebarPackage->title }}</span> <small>Package</small></a>
                @endforeach
                <a class="admin-nav-link admin-subnav-link admin-subnav-new" data-context-menu="tour-package-menu" data-page-name="China Muslim" data-context-create-url="{{ route('admin.china-muslim.create') }}" data-context-create-title="Create China Muslim package" data-context-create-label="New package" title="Right-click for package actions" href="{{ route('admin.china-muslim.create') }}">＋ New package</a>
            </nav>
        </details>
        <a class="admin-nav-link" data-context-menu="tour-package-menu" data-page-name="Company Trip (MICE)" data-edit-url="{{ route('admin.mice') }}" data-view-url="{{ route('company-trip-mice') }}" data-context-disable-create="true" data-edit-label="Manage MICE content" data-view-label="View MICE page" title="Right-click for MICE actions" href="{{ route('admin.mice') }}">
            <span class="admin-nav-icon" aria-hidden="true">▥</span> Company Trip (MICE)
        </a>
        <a class="admin-nav-link" data-context-menu="tour-package-menu" data-page-name="Smart Muslim Gallery" data-edit-url="{{ route('admin.gallery.edit') }}" data-view-url="{{ route('gallery') }}" data-context-disable-create="true" data-edit-label="Edit gallery" data-view-label="View gallery" title="Right-click for gallery actions" href="{{ route('admin.gallery.edit') }}">
            <span class="admin-nav-icon" aria-hidden="true">▦</span> Gallery
        </a>
        @foreach ($smartMuslimMenus as $smartMuslimMenu)
            <details class="admin-nav-group">
                <summary class="admin-nav-link admin-nav-parent" data-context-menu="tour-package-menu" data-page-name="{{ $smartMuslimMenu->label }}" data-edit-url="{{ route('admin.smart-muslim.menus.index') }}" data-view-url="{{ route('smart-muslim') }}" data-delete-url="{{ route('admin.smart-muslim.menus.destroy', $smartMuslimMenu) }}" data-edit-label="Manage dropdowns" data-view-label="View website" data-delete-label="Delete dropdown" data-delete-confirm="Delete this dropdown menu? Its pages must be deleted first. This cannot be undone." data-context-create-url="{{ route('admin.smart-muslim.menus.pages.create', $smartMuslimMenu) }}" data-context-create-title="Create page in {{ $smartMuslimMenu->label }}" data-context-create-label="Create page" title="Right-click for {{ $smartMuslimMenu->label }} actions">
                    <span class="admin-nav-icon smart-menu-sidebar-icon" aria-hidden="true">
                        @include('partials.smart-muslim-menu-icon', ['iconName' => $smartMuslimMenu->icon])
                    </span> {{ $smartMuslimMenu->label }}
                    <span class="admin-nav-chevron" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 7.5 5 5 5-5"/></svg>
                    </span>
                </summary>
                <nav class="admin-subnav" aria-label="{{ $smartMuslimMenu->label }}">
                    @foreach ($smartMuslimMenu->children as $childMenu)
                        <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="{{ $childMenu->label }}" data-edit-url="{{ route('admin.smart-muslim.menus.index') }}" data-view-url="{{ route('smart-muslim') }}" data-delete-url="{{ route('admin.smart-muslim.menus.destroy', $childMenu) }}" data-edit-label="Manage submenu labels" data-view-label="View website" data-delete-label="Remove submenu label" data-delete-confirm="Remove this submenu label? This cannot be undone." data-context-create-url="{{ route('admin.smart-muslim.menus.pages.create', $smartMuslimMenu) }}" data-context-create-title="Create page in {{ $smartMuslimMenu->label }}" data-context-create-label="Create page" title="Right-click for submenu actions" href="{{ route('admin.smart-muslim.menus.index') }}">{{ $childMenu->label }} <small>Label</small></a>
                    @endforeach
                    @foreach ($smartMuslimMenu->pages as $smartMuslimPage)
                        <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="{{ $smartMuslimPage->name }}" data-edit-url="{{ route('admin.tour-packages.edit', $smartMuslimPage) }}" data-view-url="{{ route('tour-package.show', $smartMuslimPage->slug) }}" data-delete-url="{{ route('admin.tour-packages.destroy', $smartMuslimPage) }}" data-edit-label="Edit page" data-view-label="View page" data-delete-label="Delete page" data-context-create-url="{{ route('admin.smart-muslim.menus.pages.create', $smartMuslimMenu) }}" data-context-create-title="Create page in {{ $smartMuslimMenu->label }}" data-context-create-label="Create another page" title="Right-click for {{ $smartMuslimPage->name }} actions" href="{{ route('admin.tour-packages.edit', $smartMuslimPage) }}">{{ $smartMuslimPage->name }} <small>{{ $smartMuslimPage->kind === 'package' ? 'Package' : 'Page' }}</small></a>
                    @endforeach
                    <a class="admin-nav-link admin-subnav-link admin-subnav-new" data-context-menu="tour-package-menu" data-page-name="{{ $smartMuslimMenu->label }}" data-context-create-url="{{ route('admin.smart-muslim.menus.pages.create', $smartMuslimMenu) }}" data-context-create-title="Create page in {{ $smartMuslimMenu->label }}" data-context-create-label="Create page" data-edit-url="{{ route('admin.smart-muslim.menus.index') }}" data-edit-label="Manage dropdowns" data-context-disable-create="false" title="Right-click for {{ $smartMuslimMenu->label }} actions" href="{{ route('admin.smart-muslim.menus.pages.create', $smartMuslimMenu) }}">＋ New page</a>
                </nav>
            </details>
        @endforeach
        <a class="admin-nav-link" data-context-menu="tour-package-menu" data-page-name="Smart Muslim content management" data-edit-url="{{ route('admin.smart-muslim.menus.index') }}" data-view-url="{{ route('smart-muslim') }}" data-context-disable-create="true" data-edit-label="Manage Smart Muslim content" data-view-label="View website" title="Right-click for Smart Muslim actions" href="{{ route('admin.smart-muslim.menus.index') }}">
            <span class="admin-nav-icon" aria-hidden="true">＋</span> Manage Smart Muslim content
        </a>
        <a class="admin-nav-link" href="{{ route('admin.contact.edit') }}">
            <span class="admin-nav-icon" aria-hidden="true">✉</span> Contact Us
        </a>
    </nav>
    </div>

    <div class="admin-sidebar-bottom">
        <div class="admin-help-card">
            <span class="admin-help-icon" aria-hidden="true">✦</span>
            <strong>Need a hand?</strong>
            <p>Our support team is here to help.</p>
            <a href="mailto:tour@roystartravel.com.my">Contact support <span aria-hidden="true">→</span></a>
        </div>
        <a class="admin-back-link" href="{{ route('home') }}">
            <span aria-hidden="true">↗</span> Back to website
        </a>
        <div class="admin-profile">
            <span class="admin-avatar" aria-hidden="true">{{ auth()->user()->initials() }}</span>
            <span><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></span>
            <span class="admin-profile-more" aria-hidden="true">···</span>
        </div>
        <form class="admin-logout-form" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="admin-logout-button" type="submit"><span aria-hidden="true">↪</span> Sign out</button>
        </form>
    </div>
</aside>
<div class="admin-context-menu" id="tour-package-menu" role="menu" hidden>
    <span class="admin-context-title" data-context-page-title hidden></span>
    <div data-context-page-actions hidden>
        <a role="menuitem" data-context-edit data-default-label="Edit page" href="#">Edit page</a>
        <a role="menuitem" data-context-view data-default-label="View page ↗" href="#" target="_blank" rel="noreferrer">View page ↗</a>
        <form method="POST" action="#" data-context-delete hidden onsubmit="return confirm(this.dataset.confirmMessage || 'Delete this page permanently? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button class="admin-context-danger" data-default-label="Delete page" role="menuitem" type="submit">Delete page</button>
        </form>
        <hr class="admin-context-divider">
    </div>
    <div data-navigation-move-actions hidden>
        <label class="admin-context-move-label" for="navigation-parent-select">Move into</label>
        <select id="navigation-parent-select" data-navigation-parent-select></select>
        <button class="admin-context-move-button" type="button" data-navigation-move>Move page</button>
        <span data-navigation-status role="status" aria-live="polite"></span>
        <hr class="admin-context-divider">
    </div>
    <span class="admin-context-title" data-context-create-title>Create new page</span>
    <div data-context-create-actions>
        <a role="menuitem" data-context-create data-default-label="Introduction page" href="{{ route('admin.tour-packages.create', ['kind' => 'introduction']) }}">Introduction page</a>
        <a role="menuitem" data-context-create data-default-label="Package page" href="{{ route('admin.tour-packages.create', ['kind' => 'package']) }}">Package page</a>
    </div>
</div>
<button class="admin-sidebar-backdrop" type="button" aria-label="Close admin navigation" hidden></button>
