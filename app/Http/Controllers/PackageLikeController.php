<?php

namespace App\Http\Controllers;

use App\Models\PackageLike;
use App\Support\PackageLikes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The love button on Smart Muslim packages. Signed-in visitors like as their account; guests get a
 * long-lived cookie so each browser counts once per package.
 */
class PackageLikeController extends Controller
{
    public const GUEST_COOKIE = 'sm_liker';

    private const GUEST_COOKIE_MINUTES = 60 * 24 * 365 * 2;

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
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:191', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! PackageLikes::exists($value)) {
                    $fail('That package could not be found.');
                }
            }],
        ]);

        $guestToken = $request->user() ? null : ($request->cookie(self::GUEST_COOKIE) ?: (string) Str::uuid());
        $visitorKey = $this->visitorKey($request, $guestToken);
        $existing = PackageLike::query()->where('package_key', $validated['key'])->where('visitor_key', $visitorKey);

        if ($existing->exists()) {
            $existing->delete();
        } else {
            PackageLike::query()->firstOrCreate(['package_key' => $validated['key'], 'visitor_key' => $visitorKey]);
        }

        $response = response()->json(PackageLikes::summary([$validated['key']], $visitorKey)[$validated['key']]);

        return $guestToken === null ? $response : $response->cookie(self::GUEST_COOKIE, $guestToken, self::GUEST_COOKIE_MINUTES);
    }

    private function visitorKey(Request $request, ?string $guestToken = null): ?string
    {
        if ($request->user()) {
            return 'user:'.$request->user()->getKey();
        }

        $token = $guestToken ?? $request->cookie(self::GUEST_COOKIE);

        return is_string($token) && Str::isUuid($token) ? 'guest:'.$token : null;
    }
}
