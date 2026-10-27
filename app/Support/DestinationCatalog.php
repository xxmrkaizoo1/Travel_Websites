<?php

namespace App\Support;

use App\Models\TourPackagePage;
use Illuminate\Support\Str;

/**
 * Destination names used on the website (tour package pages and series destinations), with a photo for each.
 */
class DestinationCatalog
{
    /** @var array<string, array{name: string, image: ?string}>|null */
    private ?array $destinations = null;

    /**
     * Destination names for suggestions, sorted alphabetically.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return collect($this->destinations())->pluck('name')->sort(SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
    }

    public function imageFor(string $destination): ?string
    {
        return $this->destinations()[$this->key($destination)]['image'] ?? null;
    }

    /**
     * @return array<string, array{name: string, image: ?string}>
     */
    private function destinations(): array
    {
        if ($this->destinations !== null) {
            return $this->destinations;
        }

        $destinations = [];
        $pages = TourPackagePage::query()->get(['name', 'hero_image', 'package_data']);

        foreach ($pages as $page) {
            $destinations[$this->key($page->name)] ??= [
                'name' => $page->name,
                'image' => filled($page->hero_image) ? $page->imageUrl($page->hero_image) : null,
            ];

            foreach ($page->package_data['series'] ?? [] as $series) {
                $photo = $series['gallery'][0]['image'] ?? null;

                $destinations[$this->key($series['name'])] = [
                    'name' => $series['name'],
                    'image' => filled($photo) ? $page->imageUrl($photo) : null,
                ];
            }
        }

        return $this->destinations = $destinations;
    }

    private function key(string $name): string
    {
        return Str::lower(trim($name));
    }
}
