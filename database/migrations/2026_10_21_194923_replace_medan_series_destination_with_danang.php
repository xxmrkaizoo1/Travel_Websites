<?php

use App\Models\TourPackagePage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const SLUG = 'series-departures-2026';

    public function up(): void
    {
        $page = TourPackagePage::query()->where('slug', self::SLUG)->first();

        if ($page === null) {
            return;
        }

        $packageData = $page->package_data ?? [];
        $series = $packageData['series'] ?? [];
        $hasMedan = false;

        foreach ($series as $index => $destination) {
            if (($destination['name'] ?? null) !== 'Medan') {
                continue;
            }

            $series[$index] = $this->daNangDestination();
            $hasMedan = true;
        }

        if (! $hasMedan) {
            return;
        }

        $packageData['series'] = $series;
        $page->package_data = $packageData;
        $page->meta_description = str_replace('Seoul, Kunming and Medan', 'Seoul, Kunming and Da Nang', $page->meta_description);
        $page->hero_note = str_replace('MEDAN', 'DA NANG', $page->hero_note);
        $page->save();
    }

    public function down(): void
    {
        $page = TourPackagePage::query()->where('slug', self::SLUG)->first();

        if ($page === null) {
            return;
        }

        $packageData = $page->package_data ?? [];
        $series = $packageData['series'] ?? [];
        $hasDaNang = false;

        foreach ($series as $index => $destination) {
            if (($destination['name'] ?? null) !== 'Da Nang') {
                continue;
            }

            $series[$index] = $this->medanDestination();
            $hasDaNang = true;
        }

        if (! $hasDaNang) {
            return;
        }

        $packageData['series'] = $series;
        $page->package_data = $packageData;
        $page->meta_description = str_replace('Seoul, Kunming and Da Nang', 'Seoul, Kunming and Medan', $page->meta_description);
        $page->hero_note = str_replace('DA NANG', 'MEDAN', $page->hero_note);
        $page->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function daNangDestination(): array
    {
        return [
            'name' => 'Da Nang',
            'country' => 'Vietnam',
            'flag' => 'images/series/flag-vn.png',
            'tagline' => 'Golden bridges, beach days and lantern-lit evenings.',
            'intro' => 'Discover Da Nang, Vietnam’s lively coastal city, with its sandy beaches, mountain scenery, famous Golden Bridge and easy access to Hoi An.',
            'advantages' => [
                ['icon' => '🌉', 'title' => 'Iconic Dragon Bridge', 'body' => 'See Da Nang’s landmark Dragon Bridge spanning the Han River, especially beautiful when it lights up at night.'],
                ['icon' => '🏖️', 'title' => 'Relaxing Beach Escapes', 'body' => 'Enjoy the long sandy shoreline and sea views at My Khe Beach.'],
                ['icon' => '🌄', 'title' => 'Golden Bridge & Mountain Views', 'body' => 'Visit the famous Golden Bridge at Ba Na Hills, held above the landscape by giant stone hands.'],
                ['icon' => '🏮', 'title' => 'Culture, Caves & Lanterns', 'body' => 'Explore the Marble Mountains and take a trip to nearby Hoi An’s historic, lantern-lit old town.'],
            ],
            'gallery' => [
                ['image' => 'images/series/danang-1.jpg', 'alt' => 'Dragon Bridge illuminated at night in Da Nang', 'caption' => 'Dragon Bridge'],
                ['image' => 'images/series/danang-2.jpg', 'alt' => 'Sandy shore and sea at My Khe Beach', 'caption' => 'My Khe Beach'],
                ['image' => 'images/series/danang-3.jpg', 'alt' => 'Golden Bridge at Ba Na Hills, Da Nang', 'caption' => 'Golden Bridge'],
                ['image' => 'images/series/danang-4.jpg', 'alt' => 'Marble Mountains overlooking Da Nang', 'caption' => 'Marble Mountains'],
                ['image' => 'images/series/danang-5.jpg', 'alt' => 'Lanterns lighting an old-town street near Da Nang', 'caption' => 'Hoi An Ancient Town'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function medanDestination(): array
    {
        return [
            'name' => 'Medan',
            'country' => 'Indonesia',
            'flag' => 'images/series/flag-id.png',
            'tagline' => 'Lake Toba, Batak culture and bold flavours.',
            'intro' => 'Taking a trip to Medan is so much fun! The capital of North Sumatra is rich in natural beauty, unique culture, delicious food, and friendly locals.',
            'advantages' => [
                ['icon' => '🏞️', 'title' => 'Stunning Natural Scenery', 'body' => 'Medan is the gateway to Lake Toba, the largest volcanic lake in the world. Visit Samosir Island, Sipiso-piso Waterfall and the cool mountains of Berastagi.'],
                ['icon' => '🌤️', 'title' => 'Cool & Comfortable Weather', 'body' => 'In highland areas like Berastagi and Samosir the weather is pleasant and refreshing, perfect for relaxing away from the big cities.'],
                ['icon' => '🥘', 'title' => 'Traditional Batak & Malay Cuisine', 'body' => 'Try nasi padang, soto medan, ayam penyet, grilled Toba fish and Batak dishes like arsik and saksang. Spicy, bold and delicious!'],
                ['icon' => '🛍️', 'title' => 'Shopping & Souvenirs', 'body' => 'Stop by Pasar Ikan Lama, Pajak Central or Merdeka Walk for ulos cloth, Sumatran coffee, layered cakes, bika ambon and more.'],
            ],
            'gallery' => [
                ['image' => 'images/series/medan-1.jpg', 'alt' => 'Sipiso-piso Waterfall', 'caption' => 'Sipiso-piso Waterfall'],
                ['image' => 'images/series/medan-2.jpg', 'alt' => 'Sunset at Lake Toba', 'caption' => 'Lake Toba'],
                ['image' => 'images/series/medan-3.jpg', 'alt' => 'Maimun Palace', 'caption' => 'Maimun Palace'],
                ['image' => 'images/series/medan-4.jpg', 'alt' => 'Great Mosque of Medan', 'caption' => 'Great Mosque of Medan'],
                ['image' => 'images/series/medan-5.jpg', 'alt' => 'Batak house on Samosir Island', 'caption' => 'Samosir Island'],
            ],
        ];
    }
};
