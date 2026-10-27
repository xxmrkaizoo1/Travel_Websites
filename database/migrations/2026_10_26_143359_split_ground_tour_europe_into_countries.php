<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Every Ground Destination card is a country, so the single "Europe" card becomes
 * United Kingdom, France and Switzerland cards, all under the Europe filter.
 */
return new class extends Migration
{
    public function up(): void
    {
        $photo = static fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=900&q=85";
        $card = static fn (string $name, string $tagline, string $image, string $alt): array => ['name' => $name, 'tagline' => $tagline, 'category' => 'Europe', 'image' => $image, 'alt' => $alt, 'link_url' => ''];

        $this->replaceCards(['Europe'], [
            $card('United Kingdom', 'London, the Cotswolds and Scotland with hotels and coach transfers.', $photo('1513635269975-59663e0ac1ad'), 'Tower Bridge and the River Thames in London from above'),
            $card('France', 'Paris, the Loire Valley and the Riviera with hotels and local guides.', $photo('1502602898657-3e91760cbb34'), 'The Eiffel Tower above the River Seine at dusk'),
            $card('Switzerland', 'Lucerne, Interlaken and the Alps with rail passes and hotels.', $photo('1530122037265-a5f1f91d3b99'), 'Chalets and a waterfall in the Lauterbrunnen valley'),
        ]);
    }

    public function down(): void
    {
        $this->replaceCards(['United Kingdom', 'France', 'Switzerland'], [[
            'name' => 'Europe',
            'tagline' => 'London, Paris, Switzerland and more with coach transfers and hotels.',
            'category' => 'Europe',
            'image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=900&q=85',
            'alt' => 'Tower Bridge and the River Thames in London from above',
            'link_url' => '',
        ]]);
    }

    /**
     * Puts the replacement cards where the first named card was and drops the named cards.
     *
     * @param  array<int, string>  $names
     * @param  array<int, array<string, string>>  $replacements
     */
    private function replaceCards(array $names, array $replacements): void
    {
        $page = DB::table('group_tours_pages')->where('kind', 'ground')->first(['id', 'tours']);
        $tours = json_decode($page->tours ?? '[]', true) ?: [];
        $position = array_search($names[0], array_column($tours, 'name'), true);

        if ($page === null || $position === false) {
            return;
        }

        $kept = array_filter($tours, static fn (array $tour): bool => ! in_array($tour['name'], $names, true));
        $before = array_filter($kept, static fn (int $index): bool => $index < $position, ARRAY_FILTER_USE_KEY);
        $after = array_filter($kept, static fn (int $index): bool => $index > $position, ARRAY_FILTER_USE_KEY);

        DB::table('group_tours_pages')->where('id', $page->id)->update([
            'tours' => json_encode([...array_values($before), ...$replacements, ...array_values($after)]),
            'updated_at' => now(),
        ]);
    }
};
