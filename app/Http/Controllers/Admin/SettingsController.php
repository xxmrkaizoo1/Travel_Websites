<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * The signed-in administrator's own account: profile, appearance, password and signed-in devices.
 */
class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('Admin.settings.edit', [
            'admin' => $request->user(),
            'sessions' => $this->sessions($request),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $validated = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
        ]);

        $admin->update($validated);

        return redirect()->route('admin.settings')->with('status', 'Your profile has been updated.');
    }

    /**
     * Saves the admin colour theme. The settings page saves it in the background and gets an empty reply.
     */
    public function updateAppearance(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'admin_theme' => ['required', Rule::in(User::ADMIN_THEMES)],
        ]);

        $request->user()->update($validated);

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('admin.settings')->with('status', 'Your appearance setting has been saved.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), 'different:current_password'],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return redirect()->route('admin.settings')->with('status', 'Your password has been changed.');
    }

    /**
     * Signs this administrator out everywhere except this browser.
     */
    public function destroyOtherSessions(Request $request): RedirectResponse
    {
        $request->validateWithBag('sessions', [
            'current_password' => ['required', 'current_password'],
        ]);

        $admin = $request->user();

        if ($this->usesDatabaseSessions()) {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $admin->getAuthIdentifier())
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        // Also stops "Keep me signed in" cookies on other devices from signing back in.
        $admin->setRememberToken(Str::random(60));
        $admin->save();

        return redirect()->route('admin.settings')->with('status', 'You have been signed out of your other devices.');
    }

    /**
     * @return Collection<int, array{browser: string, platform: string, ip: ?string, last_active: Carbon, is_current: bool}>
     */
    private function sessions(Request $request): Collection
    {
        if (! $this->usesDatabaseSessions()) {
            return collect();
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn (object $session): array => [
                'browser' => $this->browser((string) $session->user_agent),
                'platform' => $this->platform((string) $session->user_agent),
                'ip' => $session->ip_address,
                'last_active' => Carbon::createFromTimestamp($session->last_activity),
                'is_current' => $session->id === $request->session()->getId(),
            ]);
    }

    private function usesDatabaseSessions(): bool
    {
        return config('session.driver') === 'database';
    }

    private function browser(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'OPR/') => 'Opera',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Unknown browser',
        };
    }

    private function platform(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'iPhone'), str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Mac OS') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown device',
        };
    }
}
