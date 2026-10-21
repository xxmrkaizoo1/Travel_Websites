<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Smart Holidays administration dashboard.">
    <title>Admin Dashboard | Smart Holidays</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-page">
    <a class="skip-link" href="#admin-main">Skip to dashboard</a>

    <div class="admin-shell">
        <aside class="admin-sidebar" id="admin-sidebar" aria-label="Admin navigation">
            <a class="admin-brand" href="{{ route('home') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART</strong><small>HOLIDAYS ADMIN</small></span>
            </a>

            <span class="admin-nav-label">WORKSPACE</span>
            <nav class="admin-navigation">
                <a class="admin-nav-link is-active" href="#overview" aria-current="page">
                    <span class="admin-nav-icon" aria-hidden="true">⌂</span> Overview
                </a>
                <a class="admin-nav-link" href="#bookings">
                    <span class="admin-nav-icon" aria-hidden="true">▤</span> Bookings <span class="admin-nav-count">8</span>
                </a>
                <a class="admin-nav-link" href="#destinations">
                    <span class="admin-nav-icon" aria-hidden="true">◎</span> Destinations
                </a>
                <a class="admin-nav-link" href="#reports">
                    <span class="admin-nav-icon" aria-hidden="true">▥</span> Reports
                </a>
            </nav>

            <span class="admin-nav-label admin-tools-label">MANAGEMENT</span>
            <nav class="admin-navigation">
                <a class="admin-nav-link" href="#bookings">
                    <span class="admin-nav-icon" aria-hidden="true">♧</span> Customers
                </a>
                <span class="admin-nav-link admin-nav-link-disabled" aria-disabled="true">
                    <span class="admin-nav-icon" aria-hidden="true">♙</span> Team <small>Coming soon</small>
                </span>
                <span class="admin-nav-link admin-nav-link-disabled" aria-disabled="true">
                    <span class="admin-nav-icon" aria-hidden="true">⚙</span> Settings <small>Coming soon</small>
                </span>
            </nav>

            <span class="admin-nav-label admin-tools-label">SMART MUSLIM TOUR</span>
            <nav class="admin-navigation" aria-label="Smart Muslim Tour">
                <a class="admin-nav-link" href="{{ route('admin.homepage') }}">
                    <span class="admin-nav-icon" aria-hidden="true">▧</span> Smart Muslim Homepage content
                </a>
                <details class="admin-nav-group" open>
                    <summary class="admin-nav-link admin-nav-parent" data-context-menu="tour-package-menu" title="Right-click to create a new page">
                        <span class="admin-nav-icon" aria-hidden="true">◇</span> Tour Package (Private)
                        <span class="admin-nav-chevron" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 7.5 5 5 5-5"/></svg>
                        </span>
                    </summary>
                    <nav class="admin-subnav" aria-label="Tour Package (Private)">
                        <a class="admin-nav-link admin-subnav-link" href="{{ route('tour-package-private') }}">Introduction</a>
                        <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Island &amp; Honeymoon" data-edit-url="{{ route('admin.island-honeymoon') }}" data-view-url="{{ route('island-honeymoon') }}" href="{{ route('admin.island-honeymoon') }}">Island &amp; Honeymoon</a>
                                                                                                                                                                         @include('Admin.partials.tour-page-children', ['parentSlug' => 'island-honeymoon', 'depth' => 1])
                                                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Indonesia" data-page-slug="indonesia" data-edit-url="{{ route('admin.tour-packages.edit', 'indonesia') }}" data-view-url="{{ route('indonesia') }}" href="{{ route('admin.tour-packages.edit', 'indonesia') }}">Indonesia</a>
                        @include('Admin.partials.tour-page-children', ['parentSlug' => 'indonesia', 'depth' => 1])
                                                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Thailand" data-page-slug="thailand" data-edit-url="{{ route('admin.tour-packages.edit', 'thailand') }}" data-view-url="{{ route('thailand') }}" href="{{ route('admin.tour-packages.edit', 'thailand') }}">Thailand</a>
                        @include('Admin.partials.tour-page-children', ['parentSlug' => 'thailand', 'depth' => 1])
                                                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Vietnam" data-page-slug="vietnam" data-edit-url="{{ route('admin.tour-packages.edit', 'vietnam') }}" data-view-url="{{ route('vietnam') }}" href="{{ route('admin.tour-packages.edit', 'vietnam') }}">Vietnam</a>
                        @include('Admin.partials.tour-page-children', ['parentSlug' => 'vietnam', 'depth' => 1])
                                                <a class="admin-nav-link admin-subnav-link" data-context-menu="tour-package-menu" data-page-name="Philippines" data-page-slug="philippines" data-edit-url="{{ route('admin.tour-packages.edit', 'philippines') }}" data-view-url="{{ route('philippines') }}" href="{{ route('admin.tour-packages.edit', 'philippines') }}">Philippines</a>
                        @include('Admin.partials.tour-page-children', ['parentSlug' => 'philippines', 'depth' => 1])
                        @include('Admin.partials.tour-page-children', ['parentSlug' => '', 'depth' => 0])
                                                                        <a class="admin-nav-link admin-subnav-link admin-subnav-new" href="{{ route('admin.tour-packages.create') }}">＋ New page</a>
                                                                    </nav>
                                                                </details>
                <span class="admin-nav-link admin-nav-link-disabled" aria-disabled="true">
                    <span class="admin-nav-icon" aria-hidden="true">▤</span> Tour Package (Series) <small>Coming soon</small>
                </span>
                <span class="admin-nav-link admin-nav-link-disabled" aria-disabled="true">
                    <span class="admin-nav-icon" aria-hidden="true">◎</span> China Muslim <small>Coming soon</small>
                </span>
                <span class="admin-nav-link admin-nav-link-disabled" aria-disabled="true">
                    <span class="admin-nav-icon" aria-hidden="true">▥</span> Company Trip (MICE) <small>Coming soon</small>
                </span>
                <span class="admin-nav-link admin-nav-link-disabled" aria-disabled="true">
                    <span class="admin-nav-icon" aria-hidden="true">✉</span> Contact Us <small>Coming soon</small>
                </span>
            </nav>

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
                    <span class="admin-avatar" aria-hidden="true">AD</span>
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
                <a role="menuitem" data-context-edit href="#">Edit page</a>
                <a role="menuitem" data-context-view href="#" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="#" data-context-delete hidden onsubmit="return confirm('Delete this page permanently? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button class="admin-context-danger" role="menuitem" type="submit">Delete page</button>
                </form>
                <hr class="admin-context-divider">
            </div>
            <span class="admin-context-title" data-context-create-title>Create new page</span>
            <a role="menuitem" data-context-create href="{{ route('admin.tour-packages.create', ['kind' => 'introduction']) }}">Introduction page</a>
            <a role="menuitem" data-context-create href="{{ route('admin.tour-packages.create', ['kind' => 'package']) }}">Package page</a>
        </div>
        <button class="admin-sidebar-backdrop" type="button" aria-label="Close admin navigation" hidden></button>

        <div class="admin-main-wrap">
            <header class="admin-topbar">
                <button class="admin-menu-toggle" type="button" aria-expanded="false" aria-controls="admin-sidebar" aria-label="Open admin navigation">
                    <span></span><span></span><span></span>
                </button>
                <div class="admin-breadcrumb"><span>Workspace</span><span aria-hidden="true">/</span><strong>Overview</strong></div>
                <div class="admin-topbar-actions">
                    <span class="admin-today"><span aria-hidden="true">◷</span> {{ now()->format('D, d M Y') }}</span>
                    <span class="admin-icon-button" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="admin-notification-dot"></span>
                    </span>
                    <span class="admin-top-avatar" aria-label="Admin profile">AD</span>
                </div>
            </header>

            <main class="admin-content" id="admin-main">
                <section class="admin-welcome" id="overview">
                    <div>
                        <span class="admin-eyebrow">SMART HOLIDAYS · ADMIN</span>
                        <h1>Travel overview</h1>
                        <p>Here’s what’s happening with your travel business today.</p>
                    </div>
                    <a class="admin-primary-button" href="#bookings"><span aria-hidden="true">＋</span> New booking</a>
                </section>

                <section class="admin-stats" aria-label="Business overview">
                    <article class="admin-stat-card">
                        <div class="admin-stat-top"><span>Total bookings</span><span class="admin-stat-icon stat-blue" aria-hidden="true">▤</span></div>
                        <strong class="admin-stat-value">1,284</strong>
                        <div class="admin-stat-foot"><span class="admin-trend">↗ 12.8%</span><span>vs last month</span></div>
                        <svg class="admin-sparkline spark-blue" viewBox="0 0 100 34" preserveAspectRatio="none" aria-hidden="true"><path d="M0 28C12 24 12 17 25 21S42 29 52 18 68 19 77 11 90 15 100 3"/></svg>
                    </article>
                    <article class="admin-stat-card">
                        <div class="admin-stat-top"><span>Revenue</span><span class="admin-stat-icon stat-green" aria-hidden="true">＄</span></div>
                        <strong class="admin-stat-value">RM 286,450</strong>
                        <div class="admin-stat-foot"><span class="admin-trend">↗ 8.2%</span><span>vs last month</span></div>
                        <svg class="admin-sparkline spark-green" viewBox="0 0 100 34" preserveAspectRatio="none" aria-hidden="true"><path d="M0 25C10 27 15 18 24 21S39 22 48 13 61 19 71 12 86 15 100 4"/></svg>
                    </article>
                    <article class="admin-stat-card">
                        <div class="admin-stat-top"><span>Active customers</span><span class="admin-stat-icon stat-purple" aria-hidden="true">♧</span></div>
                        <strong class="admin-stat-value">3,642</strong>
                        <div class="admin-stat-foot"><span class="admin-trend">↗ 6.4%</span><span>vs last month</span></div>
                        <svg class="admin-sparkline spark-purple" viewBox="0 0 100 34" preserveAspectRatio="none" aria-hidden="true"><path d="M0 29C13 24 16 25 24 19S39 25 49 16 64 20 72 12 89 17 100 5"/></svg>
                    </article>
                    <article class="admin-stat-card">
                        <div class="admin-stat-top"><span>Pending enquiries</span><span class="admin-stat-icon stat-orange" aria-hidden="true">✉</span></div>
                        <strong class="admin-stat-value">24</strong>
                        <div class="admin-stat-foot"><span class="admin-pending">Needs attention</span><span>across all channels</span></div>
                        <svg class="admin-sparkline spark-orange" viewBox="0 0 100 34" preserveAspectRatio="none" aria-hidden="true"><path d="M0 8C12 13 15 9 25 18S42 13 52 23 65 18 77 25 90 21 100 30"/></svg>
                    </article>
                </section>

                <section class="admin-dashboard-grid" id="reports">
                    <article class="admin-panel admin-revenue-panel">
                        <div class="admin-panel-heading">
                            <div><h2>Revenue overview</h2><p>Track your income over time</p></div>
                            <span class="admin-select-button">Last 7 days <span aria-hidden="true">⌄</span></span>
                        </div>
                        <div class="admin-chart-summary"><strong>RM 48,290</strong><span class="admin-trend">↗ 9.6%</span><small>compared to previous week</small></div>
                        <div class="admin-chart" role="img" aria-label="Revenue chart trend increases through the week, with the highest value on Sunday">
                            <div class="admin-chart-y-labels"><span>RM 10k</span><span>RM 7.5k</span><span>RM 5k</span><span>RM 2.5k</span><span>RM 0</span></div>
                            <svg viewBox="0 0 700 205" preserveAspectRatio="none" aria-hidden="true">
                                <defs><linearGradient id="admin-chart-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#3477d3" stop-opacity=".2"/><stop offset="100%" stop-color="#3477d3" stop-opacity="0"/></linearGradient></defs>
                                <path class="admin-chart-gridline" d="M0 12H700M0 55H700M0 98H700M0 141H700M0 184H700"/>
                                <path class="admin-chart-area" d="M0 152C38 146 55 126 100 135S156 111 200 123 251 93 300 105 350 124 400 88 451 100 500 70 550 88 600 53 653 70 700 25V184H0Z"/>
                                <path class="admin-chart-line" d="M0 152C38 146 55 126 100 135S156 111 200 123 251 93 300 105 350 124 400 88 451 100 500 70 550 88 600 53 653 70 700 25"/>
                                <circle class="admin-chart-point" cx="700" cy="25" r="5"/>
                            </svg>
                            <div class="admin-chart-x-labels"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
                        </div>
                    </article>

                    <article class="admin-panel admin-destinations-panel" id="destinations">
                        <div class="admin-panel-heading">
                            <div><h2>Popular destinations</h2><p>Bookings by destination</p></div>
                            <a class="admin-view-link" href="#bookings" aria-label="View destination bookings">•••</a>
                        </div>
                        <div class="admin-destination-list">
                            <div class="admin-destination-row">
                                <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=120&q=75" alt="" loading="lazy">
                                <span><strong>Bali, Indonesia</strong><small>Island escape</small></span>
                                <strong class="admin-destination-total">284</strong>
                            </div>
                            <div class="admin-destination-row">
                                <img src="https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=120&q=75" alt="" loading="lazy">
                                <span><strong>Istanbul, Türkiye</strong><small>City &amp; culture</small></span>
                                <strong class="admin-destination-total">196</strong>
                            </div>
                            <div class="admin-destination-row">
                                <img src="https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=120&q=75" alt="" loading="lazy">
                                <span><strong>Tokyo, Japan</strong><small>Guided tour</small></span>
                                <strong class="admin-destination-total">158</strong>
                            </div>
                            <div class="admin-destination-row">
                                <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=120&q=75" alt="" loading="lazy">
                                <span><strong>Dubai, UAE</strong><small>City break</small></span>
                                <strong class="admin-destination-total">132</strong>
                            </div>
                        </div>
                        <a class="admin-panel-bottom-link" href="#bookings">View all destinations <span aria-hidden="true">→</span></a>
                    </article>
                </section>

                <section class="admin-panel admin-bookings-panel" id="bookings">
                    <div class="admin-panel-heading admin-bookings-heading">
                        <div><h2>Recent bookings</h2><p>Manage and track your latest customer bookings</p></div>
                        <label class="admin-search">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            <input class="admin-booking-search" type="search" placeholder="Search bookings..." aria-label="Search bookings">
                        </label>
                    </div>
                    <div class="admin-table-wrap">
                        <table class="admin-bookings-table">
                            <thead><tr><th>Customer</th><th>Destination</th><th>Travel date</th><th>Guests</th><th>Amount</th><th>Status</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                            <tbody>
                                <tr><td><span class="admin-customer"><span class="admin-customer-avatar avatar-sky">NA</span><span><strong>Nur Aisyah</strong><small>BK-2025-0184</small></span></span></td><td>Bali, Indonesia</td><td>24 Jun 2025</td><td>2 Adults</td><td class="admin-amount">RM 8,450</td><td><span class="admin-status status-confirmed">Confirmed</span></td><td><span class="admin-row-action" aria-hidden="true">···</span></td></tr>
                                <tr><td><span class="admin-customer"><span class="admin-customer-avatar avatar-lilac">MR</span><span><strong>Muhammad Rizal</strong><small>BK-2025-0183</small></span></span></td><td>Istanbul, Türkiye</td><td>02 Jul 2025</td><td>4 Adults</td><td class="admin-amount">RM 14,200</td><td><span class="admin-status status-pending">Pending</span></td><td><span class="admin-row-action" aria-hidden="true">···</span></td></tr>
                                <tr><td><span class="admin-customer"><span class="admin-customer-avatar avatar-peach">SK</span><span><strong>Siti Khadijah</strong><small>BK-2025-0182</small></span></span></td><td>Tokyo, Japan</td><td>15 Aug 2025</td><td>3 Adults</td><td class="admin-amount">RM 11,600</td><td><span class="admin-status status-confirmed">Confirmed</span></td><td><span class="admin-row-action" aria-hidden="true">···</span></td></tr>
                                <tr><td><span class="admin-customer"><span class="admin-customer-avatar avatar-mint">AF</span><span><strong>Ahmad Fauzi</strong><small>BK-2025-0181</small></span></span></td><td>Dubai, UAE</td><td>28 Sep 2025</td><td>2 Adults</td><td class="admin-amount">RM 6,800</td><td><span class="admin-status status-cancelled">Cancelled</span></td><td><span class="admin-row-action" aria-hidden="true">···</span></td></tr>
                            </tbody>
                        </table>
                        <p class="admin-empty-state" hidden>No bookings match your search.</p>
                    </div>
                    <div class="admin-table-footer"><span>Showing <strong class="admin-visible-booking-count">4</strong> of 1,284 bookings</span><a href="#bookings">View all bookings <span aria-hidden="true">→</span></a></div>
                </section>

                <footer class="admin-footer">© {{ date('Y') }} Smart Holidays by Roystar Travel &amp; Tours <span>Sample dashboard data</span></footer>
            </main>
        </div>
    </div>
</body>
</html>
