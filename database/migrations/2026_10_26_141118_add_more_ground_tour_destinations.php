<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the remaining tour destinations (China, Korea, Japan, Taiwan, Macau, Europe and Australia)
 * to the Ground Destination page and puts the cards in the brochure's order.
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private const ORDER = ['Malaysia', 'China', 'Thailand', 'Korea', 'Japan', 'Indonesia', 'Taiwan', 'Macau', 'Vietnam', 'Europe', 'Australia'];

    /** @var array<int, string> */
    private const ADDED = ['China', 'Korea', 'Japan', 'Taiwan', 'Macau', 'Europe', 'Australia'];

    public function up(): void
    {
        $page = DB::table('group_tours_pages')->where('kind', 'ground')->first(['id', 'tours']);

        if ($page === null) {
            return;
        }

        $photo = static fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=900&q=85";
        $card = static fn (string $name, string $tagline, string $image, string $alt): array => ['name' => $name, 'tagline' => $tagline, 'category' => 'International', 'image' => $image, 'alt' => $alt, 'link_url' => ''];

        $newCards = [
            $card('China', 'Shanghai, Beijing and beyond with hotels, transport and guides.', $photo('1474181487882-5abf3f0ba6c2'), 'The Shanghai skyline lit up at night across the river'),
            $card('Korea', 'Seoul palaces, Nami Island and Jeju with private transport.', 'images/series/seoul-1.jpg', 'Gyeongbokgung Palace in Seoul under a clear sky'),
            $card('Japan', 'Tokyo, Mount Fuji and Kyoto with rail passes and local guides.', $photo('1576675466969-38eeae4b41f6'), 'Mount Fuji behind a red pagoda'),
            $card('Taiwan', 'Taipei, Taichung and Sun Moon Lake with a driver-guide.', $photo('1470004914212-05527e49370b'), 'Taipei 101 rising above the city at sunset'),
            $card('Macau', 'Historic old town, resorts and an easy add-on from Hong Kong.', 'images/placeholder-photo.svg', 'Placeholder photo for Macau'),
            $card('Europe', 'London, Paris, Switzerland and more with coach transfers and hotels.', $photo('1513635269975-59663e0ac1ad'), 'Tower Bridge and the River Thames in London from above'),
            $card('Australia', 'Sydney, Melbourne and the Gold Coast with hotels and day tours.', $photo('1506973035872-a4ec16b8e8d9'), 'Sydney Opera House and harbour with ferries'),
        ];

        $tours = json_decode($page->tours ?? '[]', true) ?: [];
        $existingNames = array_map('mb_strtolower', array_column($tours, 'name'));

        foreach ($newCards as $newCard) {
            if (! in_array(mb_strtolower($newCard['name']), $existingNames, true)) {
                $tours[] = $newCard;
            }
        }

        $position = static function (array $tour): int {
            $index = array_search($tour['name'], self::ORDER, true);

            return $index === false ? count(self::ORDER) : $index;
        };

        usort($tours, static fn (array $first, array $second): int => $position($first) <=> $position($second));

        DB::table('group_tours_pages')->where('id', $page->id)->update(['tours' => json_encode($tours), 'updated_at' => now()]);
    }

    public function down(): void
    {
        $page = DB::table('group_tours_pages')->where('kind', 'ground')->first(['id', 'tours']);

        if ($page === null) {
            return;
        }

        $tours = array_values(array_filter(
            json_decode($page->tours ?? '[]', true) ?: [],
            static fn (array $tour): bool => ! in_array($tour['name'], self::ADDED, true),
        ));

        DB::table('group_tours_pages')->where('id', $page->id)->update(['tours' => json_encode($tours), 'updated_at' => now()]);
    }
};
