<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the consultant team on the Smart Muslim homepage.">
    <title>Consultant team | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#homepage-consultants-main">Skip to consultant team</a>

    <div class="homepage-editor-shell">
        <header class="homepage-editor-topbar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART</strong><small>HOLIDAYS ADMIN</small></span>
            </a>
            <nav class="homepage-editor-actions" aria-label="Admin actions">
                <a href="{{ route('admin.dashboard') }}">← Dashboard</a>
                <a href="{{ route('admin.homepage') }}">Smart Muslim Homepage editor</a>
                <a href="{{ route('smart-muslim') }}#consultants-title" target="_blank" rel="noreferrer">View website ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="homepage-consultants-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.homepage') }}">Smart Muslim Homepage content</a>
                <span aria-hidden="true">/</span>
                <strong>Consultant team</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">HAVE A QUESTION?</span>
                    <h1>Consultant team</h1>
                    <p>The people visitors can WhatsApp from the Smart Muslim homepage. Anyone without their own number sends visitors to the main line ({{ '+'.substr(\App\Models\HomepageConsultant::MAIN_WHATSAPP, 0, 4).' '.substr(\App\Models\HomepageConsultant::MAIN_WHATSAPP, 4) }}) with their name in the message.</p>
                </div>
                <span class="homepage-editor-count">{{ $consultants->count() }} of {{ \App\Models\HomepageConsultant::MAX_CONSULTANTS }} people</span>
            </section>

            @if (session('status'))
                <div class="homepage-editor-alert" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="homepage-editor-errors" role="alert">
                    <strong>Please check the highlighted fields.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($consultants->isNotEmpty())
                <form class="homepage-form-panel" method="POST" action="{{ route('admin.homepage.consultants.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div>
                            <h2>Team members</h2>
                            <p>Lower order numbers appear first. Square photos with a plain background look best.</p>
                        </div>
                    </div>

                    <div class="consultant-admin-grid">
                        @foreach ($consultants as $consultant)
                            @php($field = "consultants[{$consultant->id}]")
                            @php($old = "consultants.{$consultant->id}")
                            <article class="consultant-admin-card">
                                <div class="consultant-admin-photo">
                                    @if ($consultant->photoUrl())
                                        <img src="{{ $consultant->photoUrl() }}" alt="">
                                    @else
                                        <span aria-hidden="true">{{ $consultant->initials() }}</span>
                                    @endif
                                </div>
                                <label class="homepage-form-field"><span>Name</span><input name="{{ $field }}[name]" value="{{ old("$old.name", $consultant->name) }}" maxlength="60" required></label>
                                <label class="homepage-form-field"><span>Role</span><input name="{{ $field }}[role]" value="{{ old("$old.role", $consultant->role) }}" maxlength="80" placeholder="Travel Consultant"></label>
                                <label class="homepage-form-field"><span>WhatsApp number</span><input name="{{ $field }}[whatsapp]" value="{{ old("$old.whatsapp", $consultant->whatsapp) }}" maxlength="30" inputmode="tel" placeholder="016-550 0590"></label>
                                <label class="homepage-form-field consultant-admin-order"><span>Order</span><input type="number" name="{{ $field }}[position]" value="{{ old("$old.position", $loop->iteration) }}" min="1" max="99" required></label>
                                <label class="homepage-form-field"><span>New photo</span><input type="file" name="{{ $field }}[photo]" accept="image/jpeg,image/png,image/webp"></label>
                                <div class="consultant-admin-checks">
                                    @if ($consultant->photo_path)
                                        <label><input type="checkbox" name="{{ $field }}[remove_photo]" value="1"> Remove photo</label>
                                    @endif
                                    <label class="consultant-admin-remove"><input type="checkbox" name="{{ $field }}[remove]" value="1"> Remove from team</label>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="china-about-editor-actions">
                        <button class="admin-primary-button" type="submit">Save team <span aria-hidden="true">→</span></button>
                    </div>
                </form>
            @endif

            <form class="homepage-form-panel consultant-admin-add" method="POST" action="{{ route('admin.homepage.consultants.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="homepage-form-panel-heading">
                    <span class="homepage-form-icon" aria-hidden="true">＋</span>
                    <div>
                        <h2>Add a team member</h2>
                        <p>They appear at the end of the list. Change the order above after adding.</p>
                    </div>
                </div>
                <div class="homepage-form-grid">
                    <label class="homepage-form-field"><span>Name</span><input name="new_name" value="{{ old('new_name') }}" maxlength="60" required></label>
                    <label class="homepage-form-field"><span>Role</span><input name="new_role" value="{{ old('new_role', 'Travel Consultant') }}" maxlength="80"></label>
                    <label class="homepage-form-field"><span>WhatsApp number</span><input name="new_whatsapp" value="{{ old('new_whatsapp') }}" maxlength="30" inputmode="tel" placeholder="016-550 0590"></label>
                    <label class="homepage-form-field"><span>Photo</span><input type="file" name="new_photo" accept="image/jpeg,image/png,image/webp"></label>
                </div>
                <div class="china-about-editor-actions">
                    <button class="admin-primary-button" type="submit">Add to team <span aria-hidden="true">＋</span></button>
                </div>
            </form>
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · Website content manager</footer>
    </div>
</body>
</html>
