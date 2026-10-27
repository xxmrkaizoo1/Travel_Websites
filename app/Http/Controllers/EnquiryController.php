<?php

namespace App\Http\Controllers;

use App\Enums\EnquirySource;
use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Records enquiries sent from website forms (such as the ticket enquiry) in the admin inbox.
 */
class EnquiryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'destination' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $enquiry = new Enquiry([...$validated, 'source' => EnquirySource::Website]);
        $client = $request->user();

        if ($client !== null && ! $client->is_admin) {
            $enquiry->client()->associate($client);
        }

        $enquiry->save();

        return response()->json(['received' => true], 201);
    }
}
