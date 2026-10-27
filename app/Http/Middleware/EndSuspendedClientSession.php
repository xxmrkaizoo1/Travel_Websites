<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs a client out as soon as an administrator suspends their account.
 */
class EndSuspendedClientSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_admin && $user->isSuspended()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('client.login')->withErrors([
                'email' => 'This account has been suspended. Please contact us for help.',
            ]);
        }

        return $next($request);
    }
}
