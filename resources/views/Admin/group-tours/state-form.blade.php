<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Add or edit a state card on a Smart Holidays group tour destination page.">
    <title>{{ $stateKey ? 'Edit '.$state['name'] : 'New '.$destination->placeTerm() }} | {{ $destination->name }} | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#state-form-main">Skip to state form</a>

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
                <a href="{{ route('group-tours.destination', $destination) }}">View page</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="state-form-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.group-tours.edit') }}">Group Tours</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('admin.group-tour-destinations.edit', $destination) }}">{{ $destination->name }}</a>
                <span aria-hidden="true">/</span>
                <strong>{{ $stateKey ? $state['name'] : 'New '.$destination->placeTerm() }}</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">{{ \Illuminate\Support\Str::upper($destination->name) }} · {{ \Illuminate\Support\Str::upper($destination->placeTerm()) }} CARD</span>
                    <h1>{{ $stateKey ? 'Edit '.$state['name'] : 'Add a '.$destination->placeTerm() }}</h1>
                    <p>The card shows the photo, capital, name and short description, with an Enquire button that opens WhatsApp.</p>
                </div>
                <a class="homepage-editor-back" href="{{ route('group-tours.destination', $destination) }}">← Back to page</a>
            </section>

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

            <form class="homepage-campaign-form" method="POST" enctype="multipart/form-data"
                action="{{ $stateKey ? route('admin.group-tour-destinations.states.update', [$destination, $stateKey]) : route('admin.group-tour-destinations.states.store', $destination) }}">
                @csrf
                @if ($stateKey)
                    @method('PUT')
                @endif

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>{{ \Illuminate\Support\Str::ucfirst($destination->placeTerm()) }} details</h2><p>It is listed under its region. Without a photo the card shows a coloured tile.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>{{ \Illuminate\Support\Str::ucfirst($destination->placeTerm()) }} name</span><input name="name" value="{{ old('name', $state['name']) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Region</span>
                            <select name="region" required>
                                @foreach ($destination->regionOptions() as $regionKey => $regionLabel)
                                    <option value="{{ $regionKey }}" @selected(old('region', $state['region']) === $regionKey)>{{ $regionLabel }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="homepage-form-field"><span>{{ $destination->place_term === 'state' ? 'Capital' : 'Area or province' }} <small class="homepage-field-hint">Optional · shown as a small badge</small></span><input name="capital" value="{{ old('capital', $state['capital']) }}" maxlength="60"></label>
                        <label class="homepage-form-field"><span>Photo alt text <small class="homepage-field-hint">Optional</small></span><input name="alt" value="{{ old('alt', $state['alt']) }}" maxlength="255"></label>
                        @php($stateImage = (string) old('image', $state['image']))
                        @include('Admin.partials.image-clear-field', [
                            'value' => $stateImage,
                            'previewSrc' => filled($stateImage) ? $destination->imageUrl($stateImage) : '',
                            'urlAttributes' => 'name="image"',
                            'fileAttributes' => 'name="image_file"',
                            'defaultTitle' => 'Coloured tile',
                            'defaultHint' => 'No photo · the card shows a coloured tile with the initials',
                        ])
                        <label class="homepage-form-field homepage-form-field-wide"><span>Short description <small class="homepage-field-hint">Optional · one line</small></span><input name="tagline" value="{{ old('tagline', $state['tagline']) }}" maxlength="160"></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <a class="homepage-editor-back" href="{{ route('group-tours.destination', $destination) }}">Cancel</a>
                    <button class="admin-primary-button" type="submit">{{ $stateKey ? 'Save' : 'Add '.$destination->placeTerm() }} <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
