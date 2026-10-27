<?php

namespace App\Http\Controllers;

use App\Mail\ClientPasswordResetCode;
use App\Models\ClientPasswordReset;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * "Forgot password" for client accounts: a 6-digit code is emailed, then the client chooses a new password.
 * The same response is given whether or not the email has an account, so the form cannot be used to find accounts.
 */
class ClientPasswordResetController extends Controller
{
    private const OTP_TTL_MINUTES = 10;

    private const MAX_OTP_ATTEMPTS = 5;

    private const SESSION_KEY = 'client.reset_email';

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route($request->user()->is_admin ? 'admin.dashboard' : 'home');
        }

        return view('client.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $email = Str::lower($request->validate(['email' => ['required', 'email', 'max:255']])['email']);

        $this->sendCode($email);
        $request->session()->put(self::SESSION_KEY, $email);

        return redirect()->route('client.password.reset')
            ->with('status', 'If an account uses that email, we have sent it a 6-digit code.');
    }

    public function edit(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get(self::SESSION_KEY);

        if (! is_string($email)) {
            return redirect()->route('client.password.request');
        }

        return view('client.reset-password', ['email' => $email]);
    }

    public function update(Request $request): RedirectResponse
    {
        $email = $request->session()->get(self::SESSION_KEY);

        if (! is_string($email)) {
            return redirect()->route('client.password.request');
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        $reset = ClientPasswordReset::query()->where('email', $email)->first();
        $client = $this->clientFor($email);

        if ($reset === null || $client === null) {
            throw ValidationException::withMessages(['code' => 'That code is incorrect or has expired. Request a new code.']);
        }

        if ($reset->expires_at->isPast()) {
            $reset->delete();

            throw ValidationException::withMessages(['code' => 'Your code has expired. Request a new code to continue.']);
        }

        if ($reset->attempts >= self::MAX_OTP_ATTEMPTS) {
            throw ValidationException::withMessages(['code' => 'Too many incorrect codes. Request a new code to continue.']);
        }

        if (! Hash::check($validated['code'], $reset->otp_hash)) {
            $reset->increment('attempts');

            throw ValidationException::withMessages(['code' => 'That code is incorrect.']);
        }

        $client->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        $reset->delete();
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('client.login')
            ->with('status', 'Your password has been reset. Sign in with your new password.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get(self::SESSION_KEY);

        if (! is_string($email)) {
            return redirect()->route('client.password.request');
        }

        $this->sendCode($email);

        return redirect()->route('client.password.reset')
            ->with('status', 'If an account uses that email, we have sent it a new code.');
    }

    /**
     * Emails a fresh code to a client account; any earlier code stops working.
     */
    private function sendCode(string $email): void
    {
        $client = $this->clientFor($email);

        if ($client === null) {
            return;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        ClientPasswordReset::query()->updateOrCreate(['email' => $email], [
            'otp_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            'attempts' => 0,
        ]);

        Mail::to($client->email)->send(new ClientPasswordResetCode($client->name, $code));
    }

    private function clientFor(string $email): ?User
    {
        return User::query()->clients()->whereRaw('LOWER(email) = ?', [$email])->first();
    }
}
