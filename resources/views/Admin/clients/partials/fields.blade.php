{{-- Client account fields. Expects $client and $passwordRequired. --}}
<fieldset class="admin-form-section">
    <legend>Details</legend>
    <div class="homepage-form-grid">
        <label class="homepage-form-field"><span>Full name</span><input name="name" value="{{ old('name', $client->name) }}" required maxlength="255" autocomplete="off"></label>
        <label class="homepage-form-field"><span>Phone <small class="homepage-field-hint">Optional</small></span><input type="tel" name="phone" value="{{ old('phone', $client->phone) }}" maxlength="30" autocomplete="off"></label>
        <label class="homepage-form-field homepage-form-field-wide"><span>Email <small class="homepage-field-hint">The client signs in with this address</small></span><input type="email" name="email" value="{{ old('email', $client->email) }}" required maxlength="255" autocomplete="off"></label>
    </div>
</fieldset>

<fieldset class="admin-form-section">
    <legend>{{ $passwordRequired ? 'Temporary password' : 'Reset password' }}</legend>
    <p class="admin-form-help">{{ $passwordRequired ? 'At least 8 characters. Share it with the client privately.' : 'Leave both fields empty to keep the current password. A new password takes effect immediately.' }}</p>
    <div class="homepage-form-grid">
        <label class="homepage-form-field"><span>{{ $passwordRequired ? 'Password' : 'New password' }}</span><input type="password" name="password" minlength="8" @required($passwordRequired) autocomplete="new-password"></label>
        <label class="homepage-form-field"><span>Confirm password</span><input type="password" name="password_confirmation" minlength="8" @required($passwordRequired) autocomplete="new-password"></label>
    </div>
</fieldset>
