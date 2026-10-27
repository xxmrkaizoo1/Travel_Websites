@extends('Admin.layouts.workspace')

@section('title', 'Settings')

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">MANAGEMENT</span>
            <h1>Settings</h1>
            <p>Your administrator account: the name and email you sign in with, your password, and the devices you are signed in on.</p>
        </div>
    </section>

    <div class="admin-settings-layout">
        <aside class="admin-panel admin-settings-card" aria-label="Your account">
            <span class="admin-settings-avatar" aria-hidden="true">{{ $admin->initials() }}</span>
            <strong>{{ $admin->name }}</strong>
            <span class="admin-settings-email">{{ $admin->email }}</span>
            <span class="admin-settings-role">Administrator</span>
            <dl>
                <div><dt>Account created</dt><dd>{{ $admin->created_at?->format('j M Y') ?? '—' }}</dd></div>
                <div><dt>Signed-in devices</dt><dd>{{ $sessions->isNotEmpty() ? $sessions->count() : '—' }}</dd></div>
            </dl>
            <nav class="admin-settings-jump" aria-label="Settings sections">
                <a href="#settings-profile">Profile</a>
                <a href="#settings-password">Password</a>
                <a href="#settings-devices">Signed-in devices</a>
            </nav>
        </aside>

        <div class="admin-settings-sections">
            <form class="admin-panel admin-record-form admin-settings-section" id="settings-profile" method="POST" action="{{ route('admin.settings.profile') }}">
                @csrf
                @method('PUT')
                <div class="admin-settings-heading">
                    <span class="admin-settings-icon" aria-hidden="true">☺</span>
                    <div><h2>Profile</h2><p>Shown in the admin sidebar. You sign in with this email address.</p></div>
                </div>
                <div class="homepage-form-grid">
                    <label class="homepage-form-field">
                        <span>Full name</span>
                        <input name="name" value="{{ old('name', $admin->name) }}" required maxlength="120" autocomplete="name">
                        @error('name', 'profile') <small class="admin-settings-error">{{ $message }}</small> @enderror
                    </label>
                    <label class="homepage-form-field">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email', $admin->email) }}" required maxlength="255" autocomplete="email">
                        @error('email', 'profile') <small class="admin-settings-error">{{ $message }}</small> @enderror
                    </label>
                </div>
                <div class="admin-form-actions">
                    <button class="admin-primary-button" type="submit">Save profile <span aria-hidden="true">→</span></button>
                </div>
            </form>

            <form class="admin-panel admin-record-form admin-settings-section" id="settings-password" method="POST" action="{{ route('admin.settings.password') }}">
                @csrf
                @method('PUT')
                <div class="admin-settings-heading">
                    <span class="admin-settings-icon" aria-hidden="true">🔒</span>
                    <div><h2>Password</h2><p>Use at least 8 characters. You stay signed in on this device after changing it.</p></div>
                </div>
                <div class="homepage-form-grid">
                    <label class="homepage-form-field homepage-form-field-wide">
                        <span>Current password</span>
                        <input type="password" name="current_password" required autocomplete="current-password">
                        @error('current_password', 'password') <small class="admin-settings-error">{{ $message }}</small> @enderror
                    </label>
                    <label class="homepage-form-field">
                        <span>New password</span>
                        <input type="password" name="password" required minlength="8" autocomplete="new-password">
                        @error('password', 'password') <small class="admin-settings-error">{{ $message }}</small> @enderror
                    </label>
                    <label class="homepage-form-field">
                        <span>Confirm new password</span>
                        <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </label>
                </div>
                <div class="admin-form-actions">
                    <button class="admin-primary-button" type="submit">Change password <span aria-hidden="true">→</span></button>
                </div>
            </form>

            <form class="admin-panel admin-record-form admin-settings-section" id="settings-devices" method="POST" action="{{ route('admin.settings.sessions') }}">
                @csrf
                @method('DELETE')
                <div class="admin-settings-heading">
                    <span class="admin-settings-icon" aria-hidden="true">🖥</span>
                    <div><h2>Signed-in devices</h2><p>If you signed in on a shared or lost device, sign out everywhere else. This browser stays signed in.</p></div>
                </div>

                @if ($sessions->isNotEmpty())
                    <ul class="admin-settings-sessions">
                        @foreach ($sessions as $session)
                            <li>
                                <span class="admin-settings-device" aria-hidden="true">{{ in_array($session['platform'], ['iOS', 'Android'], true) ? '📱' : '💻' }}</span>
                                <span>
                                    <strong>{{ $session['browser'] }} on {{ $session['platform'] }}</strong>
                                    <small>{{ $session['ip'] ?? 'Unknown IP' }} · {{ $session['is_current'] ? 'Active now' : 'Last active '.$session['last_active']->diffForHumans() }}</small>
                                </span>
                                @if ($session['is_current'])
                                    <span class="admin-settings-this">This device</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="homepage-form-grid">
                    <label class="homepage-form-field homepage-form-field-wide">
                        <span>Confirm with your password</span>
                        <input type="password" name="current_password" required autocomplete="current-password">
                        @error('current_password', 'sessions') <small class="admin-settings-error">{{ $message }}</small> @enderror
                    </label>
                </div>
                <div class="admin-form-actions">
                    <button class="admin-secondary-button admin-settings-danger" type="submit">Sign out other devices</button>
                </div>
            </form>
        </div>
    </div>
@endsection
