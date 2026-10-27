<?php

namespace App\Http\Controllers;

use App\Mail\ClientEmailVerificationCode;
use App\Models\ClientEmailVerification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClientAuthController extends Controller
{
    private const OTP_TTL_MINUTES = 10;

    private const MAX_OTP_ATTEMPTS = 5;

    public function create(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->authenticatedRedirect($request)) {
            return $redirect;
        }

        return view('client.login');
    }

    public function register(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->authenticatedRedirect($request)) {
            return $redirect;
        }

        return view('client.register');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->authenticatedRedirect($request)) {
            return $redirect;
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['sometimes', 'accepted'],
        ]);

        if (! Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if ($request->user()?->is_admin || ! $request->user()?->email_verified_at) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if ($request->user()->isSuspended()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This account has been suspended. Please contact us for help.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
        if ($redirect = $this->authenticatedRedirect($request)) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_country' => ['required', 'string', Rule::in(array_keys(config('phone_countries.countries')))],
            'phone' => ['required', 'string', 'regex:/^[0-9\s\-]{4,15}$/'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        $nationalNumber = ltrim(preg_replace('/\D/', '', $validated['phone']), '0');
        $callingCode = config('phone_countries.countries.'.$validated['phone_country'].'.1');

        if (strlen($nationalNumber) < 4 || strlen($callingCode.$nationalNumber) > 15) {
            throw ValidationException::withMessages([
                'phone' => 'Please enter a valid phone number.',
            ]);
        }

        $code = $this->generateOtp();
        $verification = ClientEmailVerification::query()->updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'phone' => '+'.$callingCode.$nationalNumber,
                'password' => Hash::make($validated['password']),
                'otp_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
                'attempts' => 0,
            ],
        );

        $request->session()->put('client.pending_email', $verification->email);
        Mail::to($verification->email)->send(new ClientEmailVerificationCode($verification->name, $code));

        return redirect()->route('client.verify')->with('status', 'We sent a 6-digit verification code to your email address.');
    }

    public function verification(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('client.pending_email');
        $verification = is_string($email)
            ? ClientEmailVerification::query()->where('email', $email)->first()
            : null;

        if (! $verification) {
            $request->session()->forget('client.pending_email');

            return redirect()->route('client.register');
        }

        return view('client.verify', ['email' => $verification->email]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $email = $request->session()->get('client.pending_email');
        $verification = is_string($email)
            ? ClientEmailVerification::query()->where('email', $email)->first()
            : null;

        if (! $verification) {
            $request->session()->forget('client.pending_email');

            return redirect()->route('client.register')->withErrors([
                'email' => 'Start registration again to request a new verification code.',
            ]);
        }

        if ($verification->expires_at->isPast()) {
            $verification->delete();
            $request->session()->forget('client.pending_email');

            return redirect()->route('client.register')->withErrors([
                'email' => 'Your verification code expired. Please register again to receive a new one.',
            ]);
        }

        if ($verification->attempts >= self::MAX_OTP_ATTEMPTS) {
            throw ValidationException::withMessages([
                'code' => 'Too many incorrect codes. Request a new code to continue.',
            ]);
        }

        if (! Hash::check($validated['code'], $verification->otp_hash)) {
            $verification->increment('attempts');

            throw ValidationException::withMessages([
                'code' => 'That verification code is incorrect.',
            ]);
        }

        $user = User::query()->create([
            'name' => $verification->name,
            'email' => $verification->email,
            'phone' => $verification->phone,
            'password' => $verification->password,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $verification->delete();
        $request->session()->forget('client.pending_email');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $email = $request->session()->get('client.pending_email');
        $verification = is_string($email)
            ? ClientEmailVerification::query()->where('email', $email)->first()
            : null;

        if (! $verification) {
            $request->session()->forget('client.pending_email');

            return redirect()->route('client.register')->withErrors([
                'email' => 'Start registration again to request a new verification code.',
            ]);
        }

        $code = $this->generateOtp();
        $verification->forceFill([
            'otp_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            'attempts' => 0,
        ])->save();
        Mail::to($verification->email)->send(new ClientEmailVerificationCode($verification->name, $code));

        return redirect()->route('client.verify')->with('status', 'A new verification code has been sent to your email address.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        abort_if($request->user()?->is_admin, 403);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function authenticatedRedirect(Request $request): ?RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        return redirect()->route($user->is_admin ? 'admin.dashboard' : 'home');
    }

    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
