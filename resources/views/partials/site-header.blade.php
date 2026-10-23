    <header class="site-header smart-holidays-header">
    <div class="header-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="Smart Holidays home">
            <img class="brand-logo" src="{{ asset('images/smart-holidays-logo.png') }}" alt="Smart Holidays">
            <span class="brand-caption">ROYSTAR TRAVEL &amp; TOURS</span>
        </a>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Open navigation">
            <span></span><span></span><span></span>
        </button>

        <nav class="primary-nav" id="primary-nav" aria-label="Main navigation">
            <a href="{{ route('smart-muslim') }}#home">Group Tours</a>
            <a href="{{ route('smart-muslim') }}#services-title">Ground Tours</a>
            <a href="{{ route('smart-muslim') }}#service-company">Domestic Tour</a>
            <div class="nav-item has-dropdown">
                <button class="nav-trigger muslim-nav{{ request()->routeIs('smart-muslim', 'china-muslim', 'tour-package-private', 'island-honeymoon', 'indonesia', 'indonesia.aceh', 'bali', 'thailand', 'vietnam', 'philippines', 'tour-package.show') ? ' is-current' : '' }}" type="button" data-href="{{ route('smart-muslim') }}" aria-expanded="false" aria-controls="muslim-tours-menu">Smart Muslim Tour <span class="nav-chevron" aria-hidden="true"></span></button>
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


                             {{-- <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('indonesia') ? ' is-current' : '' }}" href="{{ route('indonesia') }}">Indonesia</a>
                                <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Indonesia destinations" aria-expanded="false" aria-controls="indonesia-destination-menu">
                                    <span class="submenu-arrow" aria-hidden="true">›</span>
                                </button>
                                <div class="submenu-menu" id="indonesia-destination-menu">
                                    @include('partials.tour-package-submenu', ['parentSlug' => 'indonesia'])
                                </div>
                            </div>
 --}}



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
                        <div class="nav-subitem has-submenu">
                            <button class="submenu-trigger" type="button" aria-expanded="false" aria-controls="series-tour-menu">
                                Tour Package (Series)
                                <span class="submenu-arrow" aria-hidden="true">›</span>
                            </button>
                            <div class="submenu-menu" id="series-tour-menu">
                                @if ($customTourPagesByParent->get('series-departures-2026', collect())->isEmpty())
                                    <a class="{{ request()->is('tour/series-departures-2026') ? 'is-current' : '' }}" href="{{ route('tour-package.show', 'series-departures-2026') }}">Introduction</a>
                                @else
                                    <div class="nav-subitem has-submenu destination-nav">
                                        <a class="destination-link{{ request()->is('tour/series-departures-2026') ? ' is-current' : '' }}" href="{{ route('tour-package.show', 'series-departures-2026') }}">Introduction</a>
                                        <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Introduction pages" aria-expanded="false" aria-controls="series-introduction-menu">
                                            <span class="submenu-arrow" aria-hidden="true">›</span>
                                        </button>
                                        <div class="submenu-menu" id="series-introduction-menu">
                                            @include('partials.tour-package-submenu', ['parentSlug' => 'series-departures-2026'])
                                        </div>
                                    </div>
                                @endif
                                @foreach ($seriesDestinations as $seriesDestination)
                                    <a href="{{ route('tour-package.show', 'series-departures-2026') }}#series-{{ \Illuminate\Support\Str::slug($seriesDestination['name']) }}">{{ $seriesDestination['name'] }} · {{ $seriesDestination['country'] }}</a>
                                @endforeach
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
            <a href="mailto:tour@roystartravel.com.my">Ticketing</a>
            <a href="#contact">Contact Us</a>
        </nav>

        <div class="header-actions">
            @auth
                @unless (auth()->user()->is_admin)
                    <button class="profile-button" type="button" data-profile-open aria-haspopup="dialog" aria-label="My profile" title="My profile">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M20 21v-1.5a4.5 4.5 0 0 0-4.5-4.5h-7A4.5 4.5 0 0 0 4 19.5V21M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                @endunless
            @endauth
            <a class="whatsapp-button" href="https://wa.me/60165500590" target="_blank" rel="noreferrer"><span class="whatsapp-icon" aria-hidden="true">◔</span> WhatsApp Us</a>
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
