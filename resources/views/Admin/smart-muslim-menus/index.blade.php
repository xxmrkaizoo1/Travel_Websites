<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage Smart Muslim Tour content, tour pages and navigation.">
    <title>Smart Muslim Content | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <div class="homepage-editor-shell smart-menu-page">
        <header class="homepage-editor-topbar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART</strong><small>HOLIDAYS ADMIN</small></span>
            </a>
            <nav class="homepage-editor-actions" aria-label="Admin actions">
                <a href="{{ route('admin.dashboard') }}">← Dashboard</a>
                <a href="{{ route('smart-muslim') }}" target="_blank" rel="noreferrer">View website ↗</a>
            </nav>
        </header>

        <main class="homepage-editor-main">
            <section class="smart-menu-hero">
                <div>
                    <span class="smart-menu-kicker"><span aria-hidden="true">✦</span> SMART MUSLIM TOUR</span>
                    <h1>Content management</h1>
                    <p>Manage the content and navigation for the whole Smart Muslim Tour section from one place.</p>
                </div>
                <div class="smart-menu-hero-stats" aria-label="Menu totals">
                    <span><strong>6</strong> content areas</span>
                    <span><strong>{{ $menus->count() }}</strong> custom dropdowns</span>
                </div>
            </section>

            @if (session('status'))
                <div class="homepage-editor-alert" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="homepage-editor-errors" role="alert">
                    <strong>Please check the following:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="smart-content-hub" aria-labelledby="smart-content-hub-heading">
                <div class="smart-menu-list-heading">
                    <div>
                        <span class="admin-eyebrow">SMART MUSLIM TOUR</span>
                        <h2 id="smart-content-hub-heading">Manage existing content</h2>
                    </div>
                    <span class="smart-menu-list-count">All content areas</span>
                </div>

                <div class="smart-content-grid">
                    <article class="smart-content-card">
                        <div class="smart-content-card-heading">
                            <span class="smart-content-card-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6"/></svg>
                            </span>
                            <div>
                                <h3>Smart Muslim homepage</h3>
                                <p>Manage homepage campaigns and service cards.</p>
                            </div>
                        </div>
                        <div class="smart-content-card-actions">
                            <a href="{{ route('admin.homepage') }}">Manage campaigns <span aria-hidden="true">→</span></a>
                            <a href="{{ route('admin.homepage.services') }}">Manage services <span aria-hidden="true">→</span></a>
                        </div>
                    </article>

                    <article class="smart-content-card">
                        <div class="smart-content-card-heading">
                            <span class="smart-content-card-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v15M2 20h20M8 7h2m4 0h2M8 11h2m4 0h2M8 15h2m4 0h2"/></svg>
                            </span>
                            <div>
                                <h3>Tour Package (Private)</h3>
                                <p>Edit existing destination and package pages.</p>
                            </div>
                        </div>
                        @if ($privatePages->isNotEmpty())
                            <ul class="smart-content-page-list">
                                @foreach ($privatePages as $page)
                                    <li>
                                        <a href="{{ route('admin.tour-packages.edit', $page) }}">{{ $page->name }}</a>
                                        <span>{{ $page->kind === 'package' ? 'Package' : 'Page' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="smart-content-empty">No private tour pages are currently available.</p>
                        @endif
                    </article>

                    <article class="smart-content-card">
                        <div class="smart-content-card-heading">
                            <span class="smart-content-card-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h10M7 13h6m-6 3h10"/></svg>
                            </span>
                            <div>
                                <h3>Tour Package (Series)</h3>
                                <p>Edit existing series departures and their pages.</p>
                            </div>
                        </div>
                        @if ($seriesPages->isNotEmpty())
                            <ul class="smart-content-page-list">
                                @foreach ($seriesPages as $page)
                                    <li>
                                        <a href="{{ route('admin.tour-packages.edit', $page) }}">{{ $page->name }}</a>
                                        <span>Series</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="smart-content-empty">No series pages are currently available.</p>
                        @endif
                    </article>

                    <article class="smart-content-card">
                        <div class="smart-content-card-heading">
                            <span class="smart-content-card-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
                            </span>
                            <div>
                                <h3>China Muslim</h3>
                                <p>Manage tour packages and the destination information popup.</p>
                            </div>
                        </div>
                        <div class="smart-content-card-actions">
                            <a href="{{ route('admin.china-muslim') }}">Manage China Muslim <span aria-hidden="true">→</span></a>
                        </div>
                    </article>

                    <article class="smart-content-card">
                        <div class="smart-content-card-heading">
                            <span class="smart-content-card-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20h18M5 20V8l7-4 7 4v12M9 11h.01M15 11h.01M9 15h.01M15 15h.01M10 20v-2h4v2"/></svg>
                            </span>
                            <div>
                                <h3>Company Trip (MICE)</h3>
                                <p>Manage page content, client logos, and gallery images.</p>
                            </div>
                        </div>
                        <div class="smart-content-card-actions">
                            <a href="{{ route('admin.mice') }}">Manage MICE content <span aria-hidden="true">→</span></a>
                        </div>
                    </article>

                    <article class="smart-content-card" id="smart-menu-navigation">
                        <div class="smart-content-card-heading">
                            <span class="smart-content-card-icon" aria-hidden="true">
                                @include('partials.smart-muslim-menu-icon', ['iconName' => 'compass'])
                            </span>
                            <div>
                                <h3>Smart Muslim navigation</h3>
                                <p>Manage custom dropdowns, submenu labels, and their pages below.</p>
                            </div>
                        </div>
                        <div class="smart-content-card-actions">
                            <a href="#create-smart-muslim-dropdown">Create a dropdown <span aria-hidden="true">↓</span></a>
                        </div>
                    </article>
                </div>
            </section>

            <section class="smart-menu-create-card" id="create-smart-muslim-dropdown">
                <div class="smart-menu-section-heading">
                    <span class="smart-menu-section-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    </span>
                    <div>
                        <h2>Create a dropdown</h2>
                        <p>Add a new heading to the Smart Muslim Tour navigation.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.smart-muslim.menus.store') }}">
                    @csrf
                    <div class="smart-menu-create-fields">
                        <label class="homepage-form-field">
                            <span>Dropdown name</span>
                            <input name="label" value="{{ old('label') }}" required maxlength="80" autofocus placeholder="e.g. Special Packages">
                        </label>
                        <button class="admin-primary-button" type="submit">Create dropdown <span aria-hidden="true">→</span></button>
                    </div>
                    @include('Admin.smart-muslim-menus.partials.icon-picker', ['fieldName' => 'icon', 'selectedIcon' => 'compass'])
                </form>
            </section>

            <section class="smart-menu-list-section" id="manage-smart-muslim-dropdowns" aria-labelledby="smart-menu-list-heading">
                <div class="smart-menu-list-heading">
                    <div>
                        <span class="admin-eyebrow">YOUR NAVIGATION</span>
                        <h2 id="smart-menu-list-heading">Dropdowns &amp; pages</h2>
                    </div>
                    <span class="smart-menu-list-count">{{ $menus->count() }} {{ \Illuminate\Support\Str::plural('dropdown', $menus->count()) }}</span>
                </div>

                <div class="smart-muslim-menu-manager">
                    @forelse ($menus as $menu)
                        <article class="smart-menu-card">
                            <header class="smart-menu-card-header">
                                <span class="smart-menu-card-icon" aria-hidden="true">
                                    @include('partials.smart-muslim-menu-icon', ['iconName' => $menu->icon])
                                </span>
                                <div class="smart-menu-card-title">
                                    <span class="smart-menu-card-eyebrow">DROPDOWN HEADING</span>
                                    <h3>{{ $menu->label }}</h3>
                                </div>
                                <span class="smart-menu-card-count">{{ $menu->pages->count() }} {{ \Illuminate\Support\Str::plural('page', $menu->pages->count()) }}</span>
                                <form method="POST" action="{{ route('admin.smart-muslim.menus.destroy', $menu) }}" onsubmit="return confirm('Delete this dropdown and all its submenu labels?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="smart-menu-delete" type="submit" @disabled($menu->pages->isNotEmpty()) title="{{ $menu->pages->isNotEmpty() ? 'Delete its pages first' : 'Delete dropdown' }}">Delete</button>
                                </form>
                            </header>

                            <form class="smart-menu-edit-form" method="POST" action="{{ route('admin.smart-muslim.menus.update', $menu) }}">
                                @csrf
                                @method('PUT')
                                <div class="smart-menu-edit-fields">
                                    <label class="homepage-form-field">
                                        <span>Dropdown name</span>
                                        <input name="label" value="{{ $menu->label }}" required maxlength="80">
                                    </label>
                                    <button class="smart-menu-save-button" type="submit">Save changes</button>
                                </div>
                                @include('Admin.smart-muslim-menus.partials.icon-picker', ['fieldName' => 'icon', 'selectedIcon' => $menu->icon])
                            </form>

                            <div class="smart-menu-card-content">
                                <div class="smart-menu-content-heading">
                                    <h4>Pages</h4>
                                    <a class="smart-menu-add-link" href="{{ route('admin.smart-muslim.menus.pages.create', $menu) }}">＋ New page</a>
                                </div>
                                @if ($menu->pages->isNotEmpty())
                                    <ul class="smart-muslim-menu-list smart-menu-pages-list">
                                        @foreach ($menu->pages as $page)
                                            <li>
                                                <span class="smart-menu-page-mark" aria-hidden="true">↗</span>
                                                <span class="smart-menu-row-label">{{ $page->name }} <small>{{ $page->kind === 'package' ? 'Package' : 'Page' }}</small></span>
                                                <a href="{{ route('admin.tour-packages.edit', $page) }}">Edit page</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="smart-menu-empty-state">No pages yet. Add a page to create a clickable link in this dropdown.</p>
                                @endif
                            </div>

                            <div class="smart-menu-card-content smart-menu-submenus">
                                <div class="smart-menu-content-heading">
                                    <h4>Text-only submenu labels</h4>
                                </div>
                                @if ($menu->children->isNotEmpty())
                                    <ul class="smart-muslim-menu-list">
                                        @foreach ($menu->children as $childMenu)
                                            <li>
                                                <span class="smart-menu-row-label">{{ $childMenu->label }}</span>
                                                <form method="POST" action="{{ route('admin.smart-muslim.menus.destroy', $childMenu) }}" onsubmit="return confirm('Delete this submenu label?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="smart-menu-delete" type="submit">Remove</button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="smart-menu-empty-state">No submenu labels added.</p>
                                @endif
                                <form class="smart-menu-add-submenu" method="POST" action="{{ route('admin.smart-muslim.menus.items.store', $menu) }}">
                                    @csrf
                                    <label class="homepage-form-field">
                                        <span class="visually-hidden">New submenu label</span>
                                        <input name="label" required maxlength="80" placeholder="Add a text-only submenu label">
                                    </label>
                                    <button type="submit">Add label <span aria-hidden="true">＋</span></button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="smart-menu-empty-card">
                            <span class="smart-menu-empty-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                            </span>
                            <h3>No dropdowns yet</h3>
                            <p>Create your first Smart Muslim Tour menu heading above.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
</body>
</html>
