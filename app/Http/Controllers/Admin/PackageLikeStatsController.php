<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageLike;
use App\Support\PackageLikes;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * How much visitors love each Smart Muslim package: totals, recent likes and the daily trend.
 */
class PackageLikeStatsController extends Controller
{
    public const SORTS = [
        'total' => 'Most loved',
        'week' => 'Most loved this week',
        'month' => 'Most loved this month',
        'recent' => 'Recently loved',
        'name' => 'Package name',
    ];

    public const GROUPS = [
        'all' => 'All packages',
        'series' => 'Series destinations',
        'tour' => 'Tour package pages',
        'china' => 'China Muslim',
    ];

    public function __invoke(Request $request): View
    {
        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'total';
        $group = array_key_exists($request->query('group'), self::GROUPS) ? $request->query('group') : 'all';
        $totals = PackageLikes::totals();
        $empty = ['total' => 0, 'week' => 0, 'month' => 0, 'members' => 0, 'last_liked_at' => null];

        $packages = PackageLikes::catalogue()
            ->map(fn (array $package): array => [...$package, ...($totals[$package['key']] ?? $empty)]);

        $rows = $packages
            ->when($group !== 'all', fn ($rows) => $rows->where('group', $group))
            ->sortBy(match ($sort) {
                'name' => [['name', 'asc']],
                'recent' => [fn (array $a, array $b): int => ($b['last_liked_at']?->getTimestamp() ?? 0) <=> ($a['last_liked_at']?->getTimestamp() ?? 0)],
                default => [[$sort, 'desc'], ['total', 'desc'], ['name', 'asc']],
            })
            ->values();

        $daily = PackageLikes::daily(14);
        $totalLikes = PackageLike::query()->count();

        return view('Admin.likes.index', [
            'rows' => $rows,
            'sort' => $sort,
            'group' => $group,
            'sorts' => self::SORTS,
            'groups' => self::GROUPS,
            'daily' => $daily,
            'dailyPeak' => max(1, ...array_column($daily, 'likes')),
            'summary' => [
                'total' => $totalLikes,
                'week' => (int) $packages->sum('week'),
                'loved' => $packages->where('total', '>', 0)->count(),
                'packages' => $packages->count(),
                'memberShare' => $totalLikes > 0 ? (int) round(PackageLike::query()->where('visitor_key', 'like', 'user:%')->count() / $totalLikes * 100) : 0,
            ],
        ]);
    }
}
