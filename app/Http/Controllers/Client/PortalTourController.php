<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Remembers that a client finished or skipped the My Account guided tour, so it no longer starts by itself.
 */
class PortalTourController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $client = $request->user();

        if (! $client->hasSeenPortalTour()) {
            $client->forceFill(['portal_tour_completed_at' => now()])->save();
        }

        return response()->noContent();
    }
}
