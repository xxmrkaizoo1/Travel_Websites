<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The client portal is for client accounts; administrators are sent to their own dashboard.
 */
class EnsureClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
