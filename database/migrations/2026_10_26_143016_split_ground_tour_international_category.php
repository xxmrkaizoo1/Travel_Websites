<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Replaces the Ground Destination page's single "International" tag with regional tags
 * (Southeast Asia, East Asia, Europe, Oceania) so visitors can filter by region.
 */
return new class extends Migration
{
    /** @var array<string, string> */
    private const REGIONS = [
        'China' => 'East Asia',
        'Korea' => 'East Asia',
        'Japan' => 'East Asia',
        'Taiwan' => 'East Asia',
        'Macau' => 'East Asia',
        'Thailand' => 'Southeast Asia',
        'Indonesia' => 'Southeast Asia',
        'Vietnam' => 'Southeast Asia',
        'Europe' => 'Europe',
        'Australia' => 'Oceania',
    ];

    public function up(): void
    {
        $this->retag(static fn (array $tour): ?string => ($tour['category'] ?? '') === 'International' ? (self::REGIONS[$tour['name']] ?? null) : null);
    }

    public function down(): void
    {
        $this->retag(static fn (array $tour): ?string => ($tour['category'] ?? '') === (self::REGIONS[$tour['name']] ?? null) ? 'International' : null);
    }

    /**
     * Gives each ground card the category the callback returns; null leaves the card unchanged.
     */
    private function retag(callable $newCategory): void
    {
        $page = DB::table('group_tours_pages')->where('kind', 'ground')->first(['id', 'tours']);

        if ($page === null) {
            return;
        }

        $tours = array_map(static function (array $tour) use ($newCategory): array {
            $category = $newCategory($tour);

            return $category === null ? $tour : [...$tour, 'category' => $category];
        }, json_decode($page->tours ?? '[]', true) ?: []);

        DB::table('group_tours_pages')->where('id', $page->id)->update(['tours' => json_encode($tours), 'updated_at' => now()]);
    }
};
