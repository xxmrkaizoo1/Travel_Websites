<?php

namespace App\Support;

use App\Models\ChinaMuslimPackage;
use App\Models\PackageLike;
use App\Models\TourPackagePage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Likes ("love") on Smart Muslim packages. A package is named by its booking route key, plus
 * "#{destination-slug}" for one destination of a series page, e.g. "tour-package/series-departures-2026#da-nang".
 */
class PackageLikes
{
    public static function key(TourPackagePage|ChinaMuslimPackage $package, ?string $destination = null): string
    {
        $key = (new BookablePackage($package))->routeKey();

        return filled($destination) ? $key.'#'.Str::slug($destination) : $key;
    }

    /**
     * Whether the key names a package that exists, so likes cannot be left on made-up keys.
     */
    public static function exists(string $key): bool
    {
        if (! preg_match('~^(tour-package|china-muslim-package)/([a-z0-9-]+)(?:#([a-z0-9-]+))?$~', $key, $match)) {
            return false;
        }

        [, $type, $id] = $match;
        $destination = $match[3] ?? null;

        if ($type === 'china-muslim-package') {
            return $destination === null && ctype_digit($id) && ChinaMuslimPackage::query()->whereKey($id)->exists();
        }

        $page = TourPackagePage::query()->where('slug', $id)->first(['id', 'slug', 'package_data']);

        if ($page === null) {
            return false;
        }

        return $destination === null || collect($page->package_data['series'] ?? [])
            ->contains(fn (array $series): bool => Str::slug($series['name'] ?? '') === $destination);
    }

    /**
     * Like counts and whether this visitor liked each package.
     *
     * @param  list<string>  $keys
     * @return array<string, array{count: int, liked: bool}>
     */
    public static function summary(array $keys, ?string $visitorKey): array
    {
        $counts = PackageLike::query()
            ->whereIn('package_key', $keys)
            ->selectRaw('package_key, count(*) as total')
            ->groupBy('package_key')
            ->pluck('total', 'package_key');

        $liked = $visitorKey === null ? collect() : PackageLike::query()
            ->whereIn('package_key', $keys)
            ->where('visitor_key', $visitorKey)
            ->pluck('package_key')
            ->flip();

        return collect($keys)
            ->mapWithKeys(fn (string $key): array => [$key => ['count' => (int) ($counts[$key] ?? 0), 'liked' => $liked->has($key)]])
            ->all();
    }

    /**
     * Every package that shows a love button: each series destination, each other tour package page
     * and each China Muslim card.
     *
     * @return Collection<int, array{key: string, name: string, context: string, group: string, url: string, image: string}>
     */
    public static function catalogue(): Collection
    {
        $tourPackages = TourPackagePage::query()->orderBy('parent_slug')->orderBy('name')->get()
            ->flatMap(function (TourPackagePage $page): array {
                $series = $page->package_data['series'] ?? [];

                if ($series === []) {
                    return [[
                        'key' => self::key($page),
                        'name' => $page->name,
                        'context' => 'Tour package page',
                        'group' => 'tour',
                        'url' => $page->publicUrl(),
                        'image' => $page->imageUrl($page->hero_image),
                    ]];
                }

                return collect($series)
                    ->filter(fn (array $destination): bool => filled($destination['name'] ?? null))
                    ->map(fn (array $destination): array => [
                        'key' => self::key($page, $destination['name']),
                        'name' => $destination['name'],
                        'context' => trim(($destination['country'] ?? '').' · '.$page->name, ' ·'),
                        'group' => 'series',
                        'url' => $page->publicUrl().'#series-'.Str::slug($destination['name']),
                        'image' => $page->imageUrl($destination['gallery'][0]['image'] ?? $page->hero_image),
                    ])
                    ->values()
                    ->all();
            });

        $chinaPackages = ChinaMuslimPackage::query()->orderBy('position')->orderBy('id')->get()
            ->map(fn (ChinaMuslimPackage $package): array => [
                'key' => self::key($package),
                'name' => $package->title,
                'context' => 'China Muslim · '.$package->formattedPrice(),
                'group' => 'china',
                'url' => route('china-muslim'),
                'image' => $package->imageUrl(),
            ]);

        return $tourPackages->concat($chinaPackages)->values();
    }

    /**
     * Like totals per package: all time, last 7 and 30 days, from signed-in clients, and the latest like.
     *
     * @return Collection<string, array{total: int, week: int, month: int, members: int, last_liked_at: ?CarbonImmutable}>
     */
    public static function totals(): Collection
    {
        return PackageLike::query()
            ->selectRaw('package_key, count(*) as total, max(created_at) as last_liked_at')
            ->selectRaw('sum(case when created_at >= ? then 1 else 0 end) as week', [now()->subDays(7)])
            ->selectRaw('sum(case when created_at >= ? then 1 else 0 end) as month', [now()->subDays(30)])
            ->selectRaw("sum(case when visitor_key like 'user:%' then 1 else 0 end) as members")
            ->groupBy('package_key')
            ->get()
            ->mapWithKeys(fn (PackageLike $row): array => [$row->package_key => [
                'total' => (int) $row->total,
                'week' => (int) $row->week,
                'month' => (int) $row->month,
                'members' => (int) $row->members,
                'last_liked_at' => $row->last_liked_at ? CarbonImmutable::parse($row->last_liked_at) : null,
            ]]);
    }

    /**
     * New likes per day, oldest first.
     *
     * @return list<array{date: CarbonImmutable, likes: int}>
     */
    public static function daily(int $days): array
    {
        $since = CarbonImmutable::today()->subDays($days - 1);
        $counts = PackageLike::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('date(created_at) as day, count(*) as likes')
            ->groupBy('day')
            ->pluck('likes', 'day');

        return collect(range(0, $days - 1))
            ->map(fn (int $offset): array => ['date' => $since->addDays($offset), 'likes' => (int) ($counts[$since->addDays($offset)->toDateString()] ?? 0)])
            ->all();
    }
}
