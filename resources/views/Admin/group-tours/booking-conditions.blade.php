<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the Smart Holidays Group Tours booking conditions.">
    <title>Booking Conditions | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#group-tours-admin-main">Skip to booking conditions</a>

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
                <a href="{{ route('group-tours.booking-conditions') }}" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="group-tours-admin-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <span>Smart Holidays</span>
                <span aria-hidden="true">/</span>
                <a href="{{ route('admin.group-tours.edit') }}">Group Tours</a>
                <span aria-hidden="true">/</span>
                <strong>Booking Conditions</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">SMART HOLIDAYS</span>
                    <h1>Booking Conditions</h1>
                    <p>Update the terms shown under Group Tours › Booking Conditions.</p>
                </div>
                <span class="homepage-editor-count">{{ count($page->booking_conditions ?? []) }} conditions</span>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.group-tours.booking-conditions.update') }}">
                @csrf
                @method('PUT')

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Conditions</h2><p>Each condition has a title and its text. Put each point on its own line to show it as a list. Add up to {{ \App\Models\GroupToursPage::MAX_BOOKING_CONDITIONS }}; empty conditions are ignored.</p></div>
                    </div>

                    @php
                        $maxConditions = \App\Models\GroupToursPage::MAX_BOOKING_CONDITIONS;
                        $conditionValues = array_values(array_filter(old('booking_conditions', $page->booking_conditions ?? []), 'is_array'));
                        $conditionValues = $conditionValues ?: [['title' => '', 'body' => '']];
                    @endphp
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxConditions }}" data-field-prefix="booking_conditions" data-item-label="Condition" data-item-label-plural="conditions" data-gallery-paged="title">
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($conditionValues as $index => $condition)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Condition {{ $index + 1 }}</legend>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Title</span><input data-gallery-field="title" name="booking_conditions[{{ $index }}][title]" value="{{ $condition['title'] ?? '' }}" maxlength="120" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Text <small class="homepage-field-hint">One point per line</small></span><textarea data-gallery-field="body" name="booking_conditions[{{ $index }}][body]" rows="7" maxlength="2000" @if ($loop->first) required @endif>{{ $condition['body'] ?? '' }}</textarea></label>
                                    <button class="island-remove-slide" type="button" data-remove-slide aria-label="Remove condition {{ $index + 1 }}">Remove condition</button>
                                </fieldset>
                            @endforeach
                        </div>
                        <div class="island-gallery-editor-actions">
                            <span data-gallery-count aria-live="polite">{{ count($conditionValues) }} of {{ $maxConditions }} conditions</span>
                            <button class="island-add-slide" type="button" data-add-slide>Add condition <span aria-hidden="true">＋</span></button>
                        </div>
                    </div>

                    <template data-gallery-template>
                        <fieldset class="island-entry-panel" data-gallery-slide>
                            <legend data-slide-legend></legend>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Title</span><input data-gallery-field="title" maxlength="120"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Text <small class="homepage-field-hint">One point per line</small></span><textarea data-gallery-field="body" rows="7" maxlength="2000"></textarea></label>
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove condition</button>
                        </fieldset>
                    </template>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Page heading</h2><p>The heading and short introduction above the conditions.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field homepage-form-field-wide"><span>Heading</span><input name="booking_heading" value="{{ old('booking_heading', $page->booking_heading) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Introduction <small class="homepage-field-hint">Optional</small></span><textarea name="booking_intro" maxlength="500">{{ old('booking_intro', $page->booking_intro) }}</textarea></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <button class="admin-primary-button" type="submit">Save booking conditions <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
