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
                <button class="nav-trigger muslim-nav{{ request()->routeIs('smart-muslim', 'tour-package-private', 'island-honeymoon', 'indonesia', 'indonesia.aceh', 'bali', 'thailand', 'vietnam', 'philippines', 'tour-package.show') ? ' is-current' : '' }}" type="button" data-href="{{ route('smart-muslim') }}" aria-expanded="false" aria-controls="muslim-tours-menu">Smart Muslim Tour <span class="nav-chevron" aria-hidden="true"></span></button>
                <div class="dropdown-menu" id="muslim-tours-menu">
                    <div class="nav-subitem has-submenu">
                        <button class="submenu-trigger" type="button" aria-expanded="false" aria-controls="private-tour-menu">
                            Tour Package (Private)
                            <span class="submenu-arrow" aria-hidden="true">›</span>
                        </button>
                        <div class="submenu-menu" id="private-tour-menu">
                            <a class="{{ request()->routeIs('tour-package-private') ? 'is-current' : '' }}" href="{{ route('tour-package-private') }}">Introduction</a>
                            <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('island-honeymoon', 'bali') ? ' is-current' : '' }}" href="{{ route('island-honeymoon') }}">Island &amp; Honeymoon</a>
                                <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Island &amp; Honeymoon destinations" aria-expanded="false" aria-controls="island-destination-menu">
                                    <span class="submenu-arrow" aria-hidden="true">›</span>
                                </button>
                                <div class="submenu-menu" id="island-destination-menu">
                                    @foreach ($customTourPagesByParent->get('island-honeymoon', collect()) as $child)
                                        <a class="{{ request()->is('tour/'.$child->slug) ? 'is-current' : '' }}" href="{{ route('tour-package.show', $child->slug) }}">{{ $child->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                            @php
                                $tourDescendants = function (string $slug) use (&$tourDescendants, $customTourPagesByParent): array {
                                    $found = [];

                                    foreach ($customTourPagesByParent->get($slug, collect()) as $child) {
                                        $found[] = $child;
                                        array_push($found, ...$tourDescendants($child->slug));
                                    }

                                    return $found;
                                };
                            @endphp
                            <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('indonesia') ? ' is-current' : '' }}" href="{{ route('indonesia') }}">Indonesia</a>
                                <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Indonesia destinations" aria-expanded="false" aria-controls="indonesia-destination-menu">
                                    <span class="submenu-arrow" aria-hidden="true">›</span>
                                </button>
                                <div class="submenu-menu" id="indonesia-destination-menu">
                                    @foreach ($tourDescendants('indonesia') as $child)
                                        <a class="{{ request()->is('tour/'.$child->slug) ? 'is-current' : '' }}" href="{{ route('tour-package.show', $child->slug) }}">{{ $child->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                            <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('thailand') ? ' is-current' : '' }}" href="{{ route('thailand') }}">Thailand</a>
                                <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Thailand destinations" aria-expanded="false" aria-controls="thailand-destination-menu">
                                    <span class="submenu-arrow" aria-hidden="true">›</span>
                                </button>
                                <div class="submenu-menu" id="thailand-destination-menu">
                                    @foreach ($tourDescendants('thailand') as $child)
                                        <a class="{{ request()->is('tour/'.$child->slug) ? 'is-current' : '' }}" href="{{ route('tour-package.show', $child->slug) }}">{{ $child->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                            <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('vietnam') ? ' is-current' : '' }}" href="{{ route('vietnam') }}">Vietnam</a>
                                <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Vietnam destinations" aria-expanded="false" aria-controls="vietnam-destination-menu">
                                    <span class="submenu-arrow" aria-hidden="true">›</span>
                                </button>
                                <div class="submenu-menu" id="vietnam-destination-menu">
                                    <a href="{{ route('vietnam') }}#destinations">Hanoi</a>
                                    <a href="{{ route('vietnam') }}#destinations">Da Nang</a>
                                    <a href="{{ route('vietnam') }}#destinations">Ho Chi Minh</a>
                                    <a href="{{ route('vietnam') }}#destinations">Hoi An</a>
                                    @foreach ($tourDescendants('vietnam') as $child)
                                        <a class="{{ request()->is('tour/'.$child->slug) ? 'is-current' : '' }}" href="{{ route('tour-package.show', $child->slug) }}">{{ $child->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                            <div class="nav-subitem has-submenu destination-nav">
                                <a class="destination-link{{ request()->routeIs('philippines') ? ' is-current' : '' }}" href="{{ route('philippines') }}">Philippines</a>
                                <button class="submenu-trigger destination-toggle" type="button" aria-label="Show Philippines destinations" aria-expanded="false" aria-controls="philippines-destination-menu">
                                    <span class="submenu-arrow" aria-hidden="true">›</span>
                                </button>
                                <div class="submenu-menu" id="philippines-destination-menu">
                                    <a href="{{ route('philippines') }}#destinations">Manila</a>
                                    <a href="{{ route('philippines') }}#destinations">Boracay</a>
                                    <a href="{{ route('philippines') }}#destinations">Palawan</a>
                                    <a href="{{ route('philippines') }}#destinations">Cebu</a>
                                    @foreach ($tourDescendants('philippines') as $child)
                                        <a class="{{ request()->is('tour/'.$child->slug) ? 'is-current' : '' }}" href="{{ route('tour-package.show', $child->slug) }}">{{ $child->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                            @foreach ($customTourPagesByParent->get('', collect()) as $customTourPage)
                                @php($customChildren = $tourDescendants($customTourPage->slug))
                                @if ($customChildren === [])
                                    <a class="{{ request()->is('tour/'.$customTourPage->slug) ? 'is-current' : '' }}" href="{{ route('tour-package.show', $customTourPage->slug) }}">{{ $customTourPage->name }}</a>
                                @else
                                    <div class="nav-subitem has-submenu destination-nav">
                                        <a class="destination-link{{ request()->is('tour/'.$customTourPage->slug) ? ' is-current' : '' }}" href="{{ route('tour-package.show', $customTourPage->slug) }}">{{ $customTourPage->name }}</a>
                                        <button class="submenu-trigger destination-toggle" type="button" aria-label="Show {{ $customTourPage->name }} destinations" aria-expanded="false" aria-controls="{{ $customTourPage->slug }}-destination-menu">
                                            <span class="submenu-arrow" aria-hidden="true">›</span>
                                        </button>
                                        <div class="submenu-menu" id="{{ $customTourPage->slug }}-destination-menu">
                                            @foreach ($customChildren as $child)
                                                <a class="{{ request()->is('tour/'.$child->slug) ? 'is-current' : '' }}" href="{{ route('tour-package.show', $child->slug) }}">{{ $child->name }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            <a href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20Egypt%20tours." target="_blank" rel="noreferrer">Egypt</a>
                            <a href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20Turkiye%20tours." target="_blank" rel="noreferrer">Turkiye</a>
                        </div>
                    </div>
                    <a href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20Muslim%20tour%20series%20packages." target="_blank" rel="noreferrer">Tour Package (Series)</a>
                    <a href="{{ route('smart-muslim') }}#service-china">China Muslim</a>
                    <a href="{{ route('smart-muslim') }}#service-company">Company Trip (MICE)</a>
                    <a href="#contact">Contact Us</a>
                </div>
            </div>
            <a href="mailto:tour@roystartravel.com.my">Ticketing</a>
            <a href="#contact">Contact Us</a>
        </nav>

        <div class="header-actions">
            <a class="whatsapp-button" href="https://wa.me/60165500590" target="_blank" rel="noreferrer"><span class="whatsapp-icon" aria-hidden="true">◔</span> WhatsApp Us</a>
            <a class="book-button" href="#contact">Book Now</a>
        </div>
    </div>
</header>
