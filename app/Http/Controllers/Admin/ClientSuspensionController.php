<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ClientSuspensionController extends Controller
{
    /**
     * Suspend the client: they can no longer sign in, and any open session ends on their next request.
     */
    public function store(User $clientAccount): RedirectResponse
    {
        $clientAccount->forceFill(['suspended_at' => now()])->save();

        return back()->with('status', "{$clientAccount->name}'s account is suspended.");
    }

    public function destroy(User $clientAccount): RedirectResponse
    {
        $clientAccount->forceFill(['suspended_at' => null])->save();

        return back()->with('status', "{$clientAccount->name}'s account is active again.");
    }
}
