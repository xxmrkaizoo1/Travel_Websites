<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Edit a featured campaign on the Smart Muslim homepage.">
    <title>Edit Smart Muslim Homepage Campaign | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#homepage-campaign-form">Skip to campaign form</a>

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
                <a href="{{ route('smart-muslim') }}" target="_blank" rel="noreferrer">View website ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="homepage-campaign-form">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.homepage') }}">Smart Muslim Homepage content</a>
                <span aria-hidden="true">/</span>
                <strong>{{ $campaign ? 'Edit campaign' : 'Add campaign' }}</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">FEATURED CAMPAIGN</span>
                    <h1>{{ $campaign ? 'Edit campaign' : 'Add campaign' }}</h1>
                    <p>{{ $campaign ? 'Change the copy, visual style, or images. Leave an image empty to keep the current one.' : 'Create a new featured Smart Muslim homepage slide with its copy, visual style, and three campaign images.' }}</p>
                </div>
                <a class="homepage-editor-back" href="{{ route('admin.homepage') }}">← All campaigns</a>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ $campaign ? route('admin.homepage.update', $campaign) : route('admin.homepage.store') }}" enctype="multipart/form-data">
                @csrf
                @if ($campaign) @method('PUT') @endif

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div>
                            <h2>Slide design</h2>
                            <p>Choose how this slide looks on the Smart Muslim homepage. You can switch at any time.</p>
                        </div>
                    </div>

                    @php($selectedLayout = old('layout', $campaign?->layout ?? 'collage'))
                    <div class="campaign-layout-picker" role="radiogroup" aria-label="Slide design" data-campaign-layout>
                        <label class="campaign-layout-option">
                            <input type="radio" name="layout" value="collage" @checked($selectedLayout === 'collage') required>
                            <span class="campaign-layout-mock campaign-layout-mock-collage" aria-hidden="true">
                                <span class="mock-photo"></span>
                                <span class="mock-circle mock-circle-top"></span>
                                <span class="mock-circle mock-circle-bottom"></span>
                                <span class="mock-panel"><i></i><i></i><i></i><b></b></span>
                            </span>
                            <span class="campaign-layout-copy">
                                <strong>Collage</strong>
                                <small>Main photo with two round photos, sticker, ribbon and brand stamp. Text sits on a blue panel. Needs 3 images.</small>
                            </span>
                        </label>
                        <label class="campaign-layout-option">
                            <input type="radio" name="layout" value="banner" @checked($selectedLayout === 'banner')>
                            <span class="campaign-layout-mock campaign-layout-mock-banner" aria-hidden="true">
                                <span class="mock-photo"></span>
                                <span class="mock-text"><i></i><i></i><i></i><b></b></span>
                            </span>
                            <span class="campaign-layout-copy">
                                <strong>Single image</strong>
                                <small>One full-width photo with the text over it. Simple and bold. Needs 1 image.</small>
                            </span>
                        </label>
                    </div>
                    @error('layout') <small class="campaign-layout-error">{{ $message }}</small> @enderror
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">Aa</span>
                        <div>
                            <h2>Campaign text</h2>
                            <p>These words appear on the featured slide.</p>
                        </div>
                    </div>

                    <div class="homepage-form-grid">
                        <label class="homepage-form-field">
                            <span>Intro line</span>
                            <input name="kicker" value="{{ old('kicker', $campaign?->kicker) }}" maxlength="100" required>
                            @error('kicker') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field">
                            <span>Main heading</span>
                            <input name="headline" value="{{ old('headline', $campaign?->headline) }}" maxlength="100" required>
                            @error('headline') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field">
                            <span>Emphasized heading</span>
                            <input name="headline_highlight" value="{{ old('headline_highlight', $campaign?->headline_highlight) }}" maxlength="100" required>
                            @error('headline_highlight') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field">
                            <span>Button text</span>
                            <input name="cta_label" value="{{ old('cta_label', $campaign?->cta_label) }}" maxlength="100" required>
                            @error('cta_label') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field">
                            <span>Campaign visual style</span>
                            <select name="design_key" required>
                                @foreach (['fair' => 'Classic', 'muslim' => 'Muslim tours', 'group' => 'Group travel'] as $key => $label)
                                    <option value="{{ $key }}" @selected(old('design_key', $campaign?->design_key ?? 'fair') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('design_key') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field homepage-form-field-wide">
                            <span>Supporting text <small>Put each short line on a new line.</small></span>
                            <textarea name="body" rows="4" maxlength="500" required>{{ old('body', $campaign?->body) }}</textarea>
                            @error('body') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">✦</span>
                        <div>
                            <h2>Slide labels</h2>
                            <p>Small decorative labels displayed over the images.</p>
                        </div>
                    </div>

                    <p class="homepage-form-field-wide" data-banner-note hidden>A normal banner slide does not use stickers, ribbons or brand stamps.</p>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field" data-collage-only>
                            <span>Image sticker <small>Use a new line to split text.</small></span>
                            <textarea name="sticker" rows="2" maxlength="100" required>{{ old('sticker', $campaign?->sticker) }}</textarea>
                            @error('sticker') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field" data-collage-only>
                            <span>Image ribbon</span>
                            <input name="ribbon" value="{{ old('ribbon', $campaign?->ribbon) }}" maxlength="100" required>
                            @error('ribbon') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field" data-collage-only>
                            <span>Brand stamp top line</span>
                            <input name="brand_top" value="{{ old('brand_top', $campaign?->brand_top) }}" maxlength="50" required>
                            @error('brand_top') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field" data-collage-only>
                            <span>Brand stamp bottom line</span>
                            <input name="brand_bottom" value="{{ old('brand_bottom', $campaign?->brand_bottom) }}" maxlength="50" required>
                            @error('brand_bottom') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">▧</span>
                        <div>
                            <h2>Campaign images</h2>
                            <p>Optional JPG, PNG, or WebP uploads up to 5 MB each.</p>
                        </div>
                    </div>

                    <div class="homepage-image-grid">
                        <label class="homepage-image-field">
                            @if ($campaign)
                                <img src="{{ $campaign->imageUrl($campaign->image_main) }}" alt="Current main campaign image">
                            @else
                                <span class="homepage-image-placeholder">Main campaign image preview</span>
                            @endif
                            <span class="homepage-image-name">Main image</span>
                            <input type="file" name="image_main" accept="image/jpeg,image/png,image/webp" @required(! $campaign)>
                            @error('image_main') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-image-field" data-collage-only>
                            @if ($campaign && filled($campaign->image_top))
                                <img src="{{ $campaign->imageUrl($campaign->image_top) }}" alt="Current upper collage image">
                            @else
                                <span class="homepage-image-placeholder">Top collage image preview</span>
                            @endif
                            <span class="homepage-image-name">Top collage image</span>
                            <input type="file" name="image_top" accept="image/jpeg,image/png,image/webp" @required(! $campaign || blank($campaign->image_top))>
                            @error('image_top') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-image-field" data-collage-only>
                            @if ($campaign && filled($campaign->image_bottom))
                                <img src="{{ $campaign->imageUrl($campaign->image_bottom) }}" alt="Current lower collage image">
                            @else
                                <span class="homepage-image-placeholder">Bottom collage image preview</span>
                            @endif
                            <span class="homepage-image-name">Bottom collage image</span>
                            <input type="file" name="image_bottom" accept="image/jpeg,image/png,image/webp" @required(! $campaign || blank($campaign->image_bottom))>
                            @error('image_bottom') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>

                    <label class="homepage-form-field homepage-alt-field">
                        <span>Main image description <small>Accessibility text for screen readers.</small></span>
                        <input name="image_alt" value="{{ old('image_alt', $campaign?->image_alt) }}" maxlength="255" required>
                        @error('image_alt') <small>{{ $message }}</small> @enderror
                    </label>
                </section>

                <div class="homepage-form-actions">
                    <a class="homepage-editor-back" href="{{ route('admin.homepage') }}">Cancel</a>
                    <button class="admin-primary-button" type="submit">{{ $campaign ? 'Save campaign' : 'Create campaign' }} <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · Website content manager</footer>
    </div>
</body>
</html>
