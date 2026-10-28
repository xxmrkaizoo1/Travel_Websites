    @php($isSmartMuslimPage = request()->routeIs('smart-muslim', 'china-muslim', 'company-trip-mice', 'gallery', 'tour-package-private', 'island-honeymoon', 'indonesia', 'indonesia.aceh', 'bali', 'thailand', 'vietnam', 'philippines', 'tour-package.show'))
    <header class="site-header smart-holidays-header">
    <div class="header-inner">
        @php($brandName = $isSmartMuslimPage ? 'Smart Muslim' : 'Smart Holidays')
        <a class="brand" href="{{ $isSmartMuslimPage ? route('smart-muslim') : route('home') }}" aria-label="{{ $brandName }} home">
            <img class="brand-logo" src="{{ asset($isSmartMuslimPage ? 'images/smart-muslim-logo.png' : 'images/smart-holidays-logo.png') }}" alt="{{ $brandName }}">
            <span class="brand-caption">ROYSTAR TRAVEL &amp; TOURS</span>
        </a>

        <button class="menu-toggle" data-tour="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Open navigation">
            <span></span><span></span><span></span>
        </button>

        <nav class="primary-nav" id="primary-nav" aria-label="Main navigation" data-tour="nav">
            <div class="nav-item has-dropdown">
                <div class="muslim-nav-control">
                    <a class="muslim-nav{{ request()->routeIs('group-tours', 'group-tours.*') ? ' is-current' : '' }}" href="{{ route('group-tours') }}">Group Tours</a>
                    <button class="nav-trigger muslim-nav-toggle" type="button" aria-label="Toggle Group Tours menu" aria-expanded="false" aria-controls="group-tours-menu"><span class="nav-chevron" aria-hidden="true"></span></button>
                </div>
                <div class="dropdown-menu" id="group-tours-menu">
                    @if ($groupTourDestinations->isEmpty())
                        <a class="{{ request()->routeIs('group-tours') ? 'is-current' : '' }}" href="{{ route('group-tours') }}" @if (request()->routeIs('group-tours')) aria-current="page" @endif>Tour Destinations</a>
                    @else
                        <div class="nav-subitem has-submenu destination-nav">
                            <a class="destination-link{{ request()->routeIs('group-tours') ? ' is-current' : '' }}" href="{{ route('group-tours') }}" @if (request()->routeIs('group-tours')) aria-current="page" @endif>Tour Destinations</a>
                            <button class="submenu-trigger destination-toggle" type="button" aria-label="Show group tour destinations" aria-expanded="false" aria-controls="group-tour-destinations-menu">
                                <span class="submenu-arrow" aria-hidden="true">›</span>
                            </button>
                            <div class="submenu-menu" id="group-tour-destinations-menu">
                                @foreach ($groupTourDestinations as $groupTourDestination)
                                    @php($isCurrentDestination = request()->routeIs('group-tours.destination') && request()->route('destination')?->is($groupTourDestination))
                                    @php($destinationRegions = $groupTourDestination->regionsInUse())
                                    @if ($destinationRegions === [])
                                        <a class="{{ $isCurrentDestination ? 'is-current' : '' }}" href="{{ route('group-tours.destination', $groupTourDestination) }}">{{ $groupTourDestination->name }}</a>
                                    @else
                                        <div class="nav-subitem has-submenu destination-nav">
                                            <a class="destination-link{{ $isCurrentDestination ? ' is-current' : '' }}" href="{{ route('group-tours.destination', $groupTourDestination) }}">{{ $groupTourDestination->name }}</a>
                                            <button class="submenu-trigger destination-toggle" type="button" aria-label="Show {{ $groupTourDestination->name }} regions" aria-expanded="false" aria-controls="group-tour-{{ $groupTourDestination->slug }}-menu">
                                                <span class="submenu-arrow" aria-hidden="true">›</span>
                                            </button>
                                            <div class="submenu-menu" id="group-tour-{{ $groupTourDestination->slug }}-menu">
                                                @foreach ($destinationRegions as $regionKey => $regionLabel)
                                                    <a href="{{ route('group-tours.destination', $groupTourDestination) }}#region-{{ $regionKey }}">{{ $regionLabel }}</a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <a class="{{ request()->routeIs('group-tours.booking-conditions') ? 'is-current' : '' }}" href="{{ route('group-tours.booking-conditions') }}" @if (request()->routeIs('group-tours.booking-conditions')) aria-current="page" @endif>Booking Conditions</a>
                </div>
            </div>
            <div class="nav-item has-dropdown">
                <div class="muslim-nav-control">
                    <a class="muslim-nav{{ request()->routeIs('ground-tours') ? ' is-current' : '' }}" href="{{ route('ground-tours') }}">Ground Destination</a>
                    <button class="nav-trigger muslim-nav-toggle" type="button" aria-label="Toggle Ground Destination menu" aria-expanded="false" aria-controls="ground-tours-menu"><span class="nav-chevron" aria-hidden="true"></span></button>
                </div>
                <div class="dropdown-menu" id="ground-tours-menu">
                    @if (blank($groundToursPage?->tours))
                        <a class="{{ request()->routeIs('ground-tours') ? 'is-current' : '' }}" href="{{ route('ground-tours') }}" @if (request()->routeIs('ground-tours')) aria-current="page" @endif>Ground Destinations</a>
                    @else
                        <div class="nav-subitem has-submenu destination-nav">
                            <a class="destination-link{{ request()->routeIs('ground-tours') ? ' is-current' : '' }}" href="{{ route('ground-tours') }}" @if (request()->routeIs('ground-tours')) aria-current="page" @endif>Ground Destinations</a>
                            <button class="submenu-trigger destination-toggle" type="button" aria-label="Show ground tour destinations" aria-expanded="false" aria-controls="ground-tour-destinations-menu">
                                <span class="submenu-arrow" aria-hidden="true">›</span>
                            </button>
                            <div class="submenu-menu" id="ground-tour-destinations-menu">
                                @foreach ($groundToursPage->tours as $groundTour)
                                    <a href="{{ $groundToursPage->tourMenuUrl($groundTour) }}">{{ $groundTour['name'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="nav-item has-dropdown">
                <div class="muslim-nav-control">
                    <a class="muslim-nav{{ request()->routeIs('domestic-tours') ? ' is-current' : '' }}" href="{{ route('domestic-tours') }}">Domestic Tour</a>
                    <button class="nav-trigger muslim-nav-toggle" type="button" aria-label="Toggle Domestic Tour menu" aria-expanded="false" aria-controls="domestic-tours-menu"><span class="nav-chevron" aria-hidden="true"></span></button>
                </div>
                <div class="dropdown-menu" id="domestic-tours-menu">
                    <a class="{{ request()->routeIs('domestic-tours') ? 'is-current' : '' }}" href="{{ route('domestic-tours') }}" @if (request()->routeIs('domestic-tours')) aria-current="page" @endif>Malaysia</a>
                </div>
            </div>
            <div class="nav-item has-dropdown">
                <div class="muslim-nav-control">
                    <a class="muslim-nav{{ $isSmartMuslimPage ? ' is-current' : '' }}" href="{{ route('smart-muslim') }}">Smart Muslim Tour</a>
                    <button class="nav-trigger muslim-nav-toggle" type="button" aria-label="Toggle Smart Muslim Tour menu" aria-expanded="false" aria-controls="muslim-tours-menu"><span class="nav-chevron" aria-hidden="true"></span></button>
                </div>
                <div class="dropdown-menu" id="muslim-tours-menu">
                    <div class="nav-subitem has-submenu">
                        <button class="submenu-trigger" type="button" aria-expanded="false" aria-controls="private-tour-menu">
                            Tour Package (Private)
                            <span class="submenu-arrow" aria-hidden="true">›</span>
                        </button>
                        <div class="submenu-menu" id="private-tour-menu">
                            <a class="{{ request()->routeIs('tour-package-private') ? 'is-current' : '' }}" href="{{ route('tour-package-private') }}">Introduction</a>
                            @php($islandHoneymoonPages = $customTourPagesByParent->get('island-honeymoon', collect()))
                            <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('island-honeymoon') ? ' is-current' : '' }}" href="{{ route('island-honeymoon') }}">Island &amp; Honeymoon</a>
                                @if ($islandHoneymoonPages->isNotEmpty())
                                    <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Island &amp; Honeymoon destinations" aria-expanded="false" aria-controls="island-destination-menu">
                                        <span class="submenu-arrow" aria-hidden="true">›</span>
                                    </button>
                                    <div class="submenu-menu" id="island-destination-menu">
                                        @include('partials.tour-package-submenu', ['parentSlug' => 'island-honeymoon'])
                                    </div>
                                @endif
                            </div>
                            <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('indonesia') ? ' is-current' : '' }}" href="{{ route('indonesia') }}">Indonesia</a>
                                <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Indonesia destinations" aria-expanded="false" aria-controls="indonesia-destination-menu">
                                    <span class="submenu-arrow" aria-hidden="true">›</span>
                                </button>
                                <div class="submenu-menu" id="indonesia-destination-menu">
                                    @include('partials.tour-package-submenu', ['parentSlug' => 'indonesia'])
                                </div>
                            </div>


                            <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('thailand') ? ' is-current' : '' }}" href="{{ route('thailand') }}">Thailand</a>
                                <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Thailand destinations" aria-expanded="false" aria-controls="thailand-destination-menu">
                                    <span class="submenu-arrow" aria-hidden="true">›</span>
                                </button>
                                <div class="submenu-menu" id="thailand-destination-menu">
                                    @include('partials.tour-package-submenu', ['parentSlug' => 'thailand'])
                                </div>
                            </div>
                            <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('vietnam') ? ' is-current' : '' }}" href="{{ route('vietnam') }}">Vietnam</a>
                                <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Vietnam destinations" aria-expanded="false" aria-controls="vietnam-destination-menu">
                                    <span class="submenu-arrow" aria-hidden="true">›</span>
                                </button>
                                <div class="submenu-menu" id="vietnam-destination-menu">
                                    @include('partials.tour-package-submenu', ['parentSlug' => 'vietnam'])
                                </div>
                            </div>
                            <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('philippines') ? ' is-current' : '' }}" href="{{ route('philippines') }}">Philippines</a>
                                <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Philippines destinations" aria-expanded="false" aria-controls="philippines-destination-menu">
                                    <span class="submenu-arrow" aria-hidden="true">›</span>
                                </button>
                                <div class="submenu-menu" id="philippines-destination-menu">
                                    @include('partials.tour-package-submenu', ['parentSlug' => 'philippines'])
                                </div>
                            </div>
                            @foreach ($customTourPagesByParent->get('', collect())->reject(fn ($customPage) => $customPage->slug === 'series-departures-2026') as $customTourPage)
                                @php($customChildren = $customTourPagesByParent->get($customTourPage->slug, collect()))
                                @if ($customChildren->isEmpty())
                                    <a class="{{ request()->is('tour/'.$customTourPage->slug) ? 'is-current' : '' }}" href="{{ route('tour-package.show', $customTourPage->slug) }}">{{ $customTourPage->name }}</a>
                                @else
                                    <div class="nav-subitem has-submenu destination-nav">
                                        <a class="destination-link{{ request()->is('tour/'.$customTourPage->slug) ? ' is-current' : '' }}" href="{{ route('tour-package.show', $customTourPage->slug) }}">{{ $customTourPage->name }}</a>
                                        <button class="submenu-trigger destination-toggle" type="button" aria-label="Show {{ $customTourPage->name }} destinations" aria-expanded="false" aria-controls="{{ $customTourPage->slug }}-destination-menu">
                                            <span class="submenu-arrow" aria-hidden="true">›</span>
                                        </button>
                                        <div class="submenu-menu" id="{{ $customTourPage->slug }}-destination-menu">
                                            @include('partials.tour-package-submenu', ['parentSlug' => $customTourPage->slug])
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @if ($customTourPages->contains('slug', 'series-departures-2026'))
                        @php($seriesNavEntries = $customTourPagesByParent->get('series-departures-2026', collect())->map(fn ($seriesPage) => ['slug' => $seriesPage->slug, 'label' => $seriesPage->name])->values())
                        @php($hasSeriesPageSlides = $seriesNavEntries->isNotEmpty())
                        @php($seriesIntroductionName = $customTourPages->firstWhere('slug', 'series-departures-2026')?->name ?? 'Series Departures 2026')
                        <div class="nav-subitem has-submenu">
                            <button class="submenu-trigger" type="button" aria-expanded="false" aria-controls="series-tour-menu">
                                Tour Package (Series)
                                <span class="submenu-arrow" aria-hidden="true">›</span>
                            </button>
                            <div class="submenu-menu" id="series-tour-menu">
                                @if ($hasSeriesPageSlides)
                                    <a class="series-nav-page" href="{{ route('tour-package.show', 'series-departures-2026') }}#page-series-departures-2026" data-series-page-target="series-departures-2026" aria-current="page">{{ $seriesIntroductionName }}</a>
                                @else
                                    <a class="{{ request()->is('tour/series-departures-2026') ? 'is-current' : '' }}" href="{{ route('tour-package.show', 'series-departures-2026') }}">{{ $seriesIntroductionName }}</a>
                                @endif
                                @foreach ($seriesNavEntries->take(4) as $seriesNavEntry)
                                    <a class="series-nav-page" href="{{ route('tour-package.show', 'series-departures-2026') }}#page-{{ $seriesNavEntry['slug'] }}" data-series-page-target="{{ $seriesNavEntry['slug'] }}">{{ $seriesNavEntry['label'] }}</a>
                                @endforeach
                                @if ($seriesNavEntries->count() > 4)
                                    <div id="series-extra-destinations" hidden>
                                        @foreach ($seriesNavEntries->skip(4) as $seriesNavEntry)
                                            <a class="series-nav-page" href="{{ route('tour-package.show', 'series-departures-2026') }}#page-{{ $seriesNavEntry['slug'] }}" data-series-page-target="{{ $seriesNavEntry['slug'] }}">{{ $seriesNavEntry['label'] }}</a>
                                        @endforeach
                                    </div>
                                    <button class="series-nav-more" type="button" data-series-nav-more aria-expanded="false" aria-controls="series-extra-destinations">
                                        <span data-series-nav-more-label>See more</span><span aria-hidden="true">＋</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <a href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20Muslim%20tour%20series%20packages." target="_blank" rel="noreferrer">Tour Package (Series)</a>
                    @endif
                    <a class="{{ request()->routeIs('china-muslim') ? 'is-current' : '' }}" href="{{ route('china-muslim') }}">China Muslim</a>
                    <a class="{{ request()->routeIs('company-trip-mice') ? 'is-current' : '' }}" href="{{ route('company-trip-mice') }}">Company Trip (MICE)</a>
                    @foreach ($smartMuslimMenus as $smartMuslimMenu)
                        <div class="nav-subitem has-submenu">
                            <button class="submenu-trigger" type="button" aria-expanded="false" aria-controls="smart-muslim-menu-{{ $smartMuslimMenu->id }}">
                                <span>{{ $smartMuslimMenu->label }}</span>
                                <span class="submenu-arrow" aria-hidden="true">›</span>
                            </button>
                            <div class="submenu-menu" id="smart-muslim-menu-{{ $smartMuslimMenu->id }}">
                                @foreach ($smartMuslimMenu->children as $childMenu)
                                    <span class="smart-muslim-menu-label">{{ $childMenu->label }}</span>
                                @endforeach
                                @foreach ($smartMuslimMenu->pages as $smartMuslimPage)
                                    <a class="{{ request()->is('tour/'.$smartMuslimPage->slug) ? 'is-current' : '' }}" href="{{ route('tour-package.show', $smartMuslimPage->slug) }}">{{ $smartMuslimPage->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <a href="{{ route('gallery') }}">Our Gallery</a>
                </div>
            </div>
            <a class="{{ request()->routeIs('ticketing') ? 'is-current' : '' }}" href="{{ route('ticketing') }}" @if (request()->routeIs('ticketing')) aria-current="page" @endif>Ticketing</a>
            <a class="{{ request()->routeIs('contact') ? 'is-current' : '' }}" href="{{ route('contact') }}" @if (request()->routeIs('contact')) aria-current="page" @endif>Contact Us</a>

            {{-- Shown at the foot of the phone and tablet menu only. --}}
            <div class="nav-panel-help">
                <p><strong>Need help planning?</strong> <span>Chat with our travel team on WhatsApp.</span></p>
                <a class="nav-panel-whatsapp" href="https://wa.me/60165500590" target="_blank" rel="noreferrer">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.5 19.5 5.6 16A8 8 0 1 1 8.4 18.6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9.3 9.2c.2 2.1 3.2 5.1 5.4 5.4l1-1.2-1.8-1-1 .8a5 5 0 0 1-2.2-2.2l.8-1-1-1.8Z" fill="currentColor"/></svg>
                    WhatsApp us
                </a>
            </div>
        </nav>

        <div class="header-actions">
            @auth
                @unless (auth()->user()->is_admin)
                    <button class="profile-button" type="button" data-tour="account" data-profile-open aria-haspopup="dialog" aria-label="My profile" title="My profile">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M20 21v-1.5a4.5 4.5 0 0 0-4.5-4.5h-7A4.5 4.5 0 0 0 4 19.5V21M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                @endunless
            @endauth
            <a class="whatsapp-button" data-tour="whatsapp" href="https://wa.me/60165500590" target="_blank" rel="noreferrer"><span class="whatsapp-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M4.5 19.5 5.6 16A8 8 0 1 1 8.4 18.6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9.3 9.2c.2 2.1 3.2 5.1 5.4 5.4l1-1.2-1.8-1-1 .8a5 5 0 0 1-2.2-2.2l.8-1-1-1.8Z" fill="currentColor"/></svg></span> WhatsApp Us</a>
            <a class="book-button" href="#contact">Book Now</a>
        </div>
    </div>
</header>

@guest
    <a class="client-portal-fab" href="{{ route('client.login') }}" aria-label="Client login or sign up" title="Client login or sign up">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-4A4.5 4.5 0 0 0 3 18.5V20M9.5 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM17 8h4m-2-2v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Client login / sign up
    </a>
@else
    @unless (auth()->user()->is_admin)
        @php($tourPage = collect(['home' => 'home', 'dashboard' => 'client.dashboard', 'profile' => 'client.profile.*', 'bookings' => 'client.bookings.index', 'requests' => 'client.enquiries.*'])->search(fn (string $routePattern): bool => request()->routeIs($routePattern)) ?: 'other')
        {{-- Settings for the client guided tour (resources/js/app.js); it starts by itself on the homepage after a client's first sign-in. --}}
        <div hidden
            data-portal-tour
            data-tour-page="{{ $tourPage }}"
            data-tour-autostart="{{ $tourPage === 'home' && ! auth()->user()->hasSeenPortalTour() ? 'true' : 'false' }}"
            data-tour-complete-url="{{ route('client.tour.complete') }}"
            data-tour-token="{{ csrf_token() }}"
            data-tour-pages="{{ json_encode([
                'home' => route('home'),
                'dashboard' => route('client.dashboard'),
                'profile' => route('client.profile.edit'),
                'bookings' => route('client.bookings.index'),
                'requests' => route('client.enquiries.index'),
            ]) }}"
            data-tour-name="{{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->first() }}"></div>
        <dialog class="profile-dialog" id="profile-dialog" aria-labelledby="profile-dialog-title">
            <div class="profile-cover">
                <button class="profile-dialog-close" type="button" data-profile-close aria-label="Close profile">×</button>
            </div>
            <div class="profile-body">
                <span class="profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <h2 id="profile-dialog-title">{{ auth()->user()->name }}</h2>
                <p class="profile-dialog-role">Smart Holidays client</p>
                @if (auth()->user()->email_verified_at)
                    <span class="profile-badge">✓ Email verified</span>
                @endif
                <dl class="profile-dialog-list">
                    <div>
                        <span class="profile-row-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <span><dt>Email</dt><dd>{{ auth()->user()->email }}</dd></span>
                    </div>
                    <div>
                        <span class="profile-row-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <span><dt>Phone</dt><dd>{{ auth()->user()->phone ?: 'Not provided' }}</dd></span>
                    </div>
                    <div>
                        <span class="profile-row-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v14H4zM4 10h16M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <span><dt>Member since</dt><dd>{{ auth()->user()->created_at?->format('d M Y') }}</dd></span>
                    </div>
                </dl>
                <section class="profile-bookings" aria-labelledby="profile-bookings-title">
                    <h3 id="profile-bookings-title">My bookings</h3>
                    @forelse ($clientBookings as $clientBooking)
                        <article class="profile-booking">
                            <div>
                                <strong>{{ $clientBooking->destination }}</strong>
                                <small>{{ $clientBooking->reference }} · {{ $clientBooking->travel_date->format('d M Y') }} · {{ $clientBooking->guestsLabel() }}</small>
                            </div>
                            <span class="profile-booking-status is-{{ $clientBooking->status->value }}">{{ $clientBooking->status->label() }}</span>
                        </article>
                    @empty
                        <p class="profile-bookings-empty">No bookings yet. When our team books a trip for you, it will appear here.</p>
                    @endforelse
                    @if ($clientBookings->isNotEmpty())
                        <a class="profile-bookings-link" href="{{ route('client.bookings.index') }}">See all my bookings →</a>
                    @endif
                </section>
                <a class="profile-account-button" href="{{ route('client.dashboard') }}">Open my account</a>
                <form method="POST" action="{{ route('client.logout') }}">
                    @csrf
                    <button class="profile-signout" type="submit">Sign out</button>
                </form>
            </div>
        </dialog>
        <script>
            (function () {
                var dialog = document.getElementById('profile-dialog');
                var open = document.querySelector('[data-profile-open]');
                if (!dialog || !open) { return; }
                open.addEventListener('click', function () { dialog.showModal(); });
                dialog.querySelector('[data-profile-close]').addEventListener('click', function () { dialog.close(); });
                dialog.addEventListener('click', function (event) { if (event.target === dialog) { dialog.close(); } });
            })();
        </script>
        <form class="client-portal-fab-form" method="POST" action="{{ route('client.logout') }}">
            @csrf
            <button class="client-portal-fab" type="submit" aria-label="Sign out of client account" title="Sign out">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M10 17l5-5-5-5M15 12H3m9-8h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Sign out
            </button>
        </form>
    @endunless
@endif

@if ($isSmartMuslimPage)
    <a class="smart-holidays-fab" href="{{ route('home') }}" aria-label="Back to the Smart Holidays main page" title="Back to Smart Holidays">
        <span class="smart-holidays-fab-arrow" aria-hidden="true">←</span>
        <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
        <span class="smart-holidays-fab-text"><small>Back to</small>Smart Holidays</span>
    </a>
@endif

@include('partials.language-switcher')
