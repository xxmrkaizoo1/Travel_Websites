@extends('Admin.layouts.workspace')

@php($breadcrumbParent = ['Enquiries', route('admin.enquiries.index')])

@section('title', 'Log enquiry')

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">ENQUIRIES</span>
            <h1>Log an enquiry</h1>
            <p>Record a phone call, WhatsApp chat, email or walk-in so the team can follow it up. If the email matches a client account, the enquiry is linked to that client.</p>
        </div>
    </section>

    <form class="admin-panel admin-record-form" method="POST" action="{{ route('admin.enquiries.store') }}">
        @csrf
        <fieldset class="admin-form-section">
            <legend>Who is asking</legend>
            <div class="homepage-form-grid">
                <label class="homepage-form-field"><span>Name</span><input name="name" value="{{ old('name') }}" required maxlength="120"></label>
                <label class="homepage-form-field">
                    <span>Came in by</span>
                    <select name="source" required>
                        @foreach (\App\Enums\EnquirySource::cases() as $source)
                            <option value="{{ $source->value }}" @selected(old('source', \App\Enums\EnquirySource::WhatsApp->value) === $source->value)>{{ $source->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="homepage-form-field"><span>Phone <small class="homepage-field-hint">Phone or email is required</small></span><input type="tel" name="phone" value="{{ old('phone') }}" maxlength="30"></label>
                <label class="homepage-form-field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}" maxlength="255"></label>
            </div>
        </fieldset>

        <fieldset class="admin-form-section">
            <legend>What they want</legend>
            <div class="homepage-form-grid">
                <label class="homepage-form-field homepage-form-field-wide">
                    <span>Destination <small class="homepage-field-hint">Optional</small></span>
                    <input name="destination" list="enquiry-destinations" value="{{ old('destination') }}" maxlength="120">
                    <datalist id="enquiry-destinations">
                        @foreach ($destinations as $destinationName)
                            <option value="{{ $destinationName }}"></option>
                        @endforeach
                    </datalist>
                </label>
                <label class="homepage-form-field homepage-form-field-wide"><span>Message</span><textarea name="message" required maxlength="2000" rows="5" placeholder="Dates, number of travellers, budget, special requests…">{{ old('message') }}</textarea></label>
            </div>
        </fieldset>

        <div class="admin-form-actions">
            <a class="admin-secondary-button" href="{{ route('admin.enquiries.index') }}">Cancel</a>
            <button class="admin-primary-button" type="submit">Save enquiry <span aria-hidden="true">→</span></button>
        </div>
    </form>
@endsection
