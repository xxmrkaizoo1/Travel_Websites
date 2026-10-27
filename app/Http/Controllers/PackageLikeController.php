<?php

namespace App\Http\Controllers;

use App\Models\PackageLike;
use App\Support\PackageLikes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The love button on Smart Muslim packages. Only signed-in client accounts can like, once per package;
 * guests are asked to sign in or sign up and come back to the same page afterwards.
 */
class PackageLikeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keys' => ['required', 'array', 'max:60'],
            'keys.*' => ['string', 'max:191'],
        ]);

        return response()->json(PackageLikes::summary(array_values(array_unique($validated['keys'])), $this->visitorKey($request)));
    }

    public function toggle(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            $request->session()->put('url.intended', url()->previous());

            return response()->json([
                'message' => 'Please log in or sign up to love this package.',
                'login_url' => route('client.login'),
                'register_url' => route('client.register'),
            ], 401);
        }

        if ($user->is_admin) {
            return response()->json(['message' => 'Only client accounts can love packages.'], 403);
        }

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:191', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! PackageLikes::exists($value)) {
                    $fail('That package could not be found.');
                }
            }],
        ]);

        $visitorKey = $this->visitorKey($request);
        $existing = PackageLike::query()->where('package_key', $validated['key'])->where('visitor_key', $visitorKey);

        if ($existing->exists()) {
            $existing->delete();
        } else {
            PackageLike::query()->firstOrCreate(['package_key' => $validated['key'], 'visitor_key' => $visitorKey]);
        }

        return response()->json(PackageLikes::summary([$validated['key']], $visitorKey)[$validated['key']]);
    }

    private function visitorKey(Request $request): ?string
    {
        return $request->user() ? 'user:'.$request->user()->getKey() : null;
    }
}
