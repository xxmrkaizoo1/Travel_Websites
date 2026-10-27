<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('client.portal.profile', ['client' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s()-]{6,20}$/'],
        ], [
            'phone.regex' => 'Please enter a valid phone number, e.g. +60 12-345 6789.',
        ]);

        $phone = preg_replace('/[^0-9+]/', '', $validated['phone']);

        $request->user()->update([
            'name' => $validated['name'],
            'phone' => str_starts_with($phone, '+') ? $phone : '+'.preg_replace('/^0/', '60', $phone),
        ]);

        return redirect()->route('client.profile.edit')->with('status', 'Your details have been updated.');
    }
}
