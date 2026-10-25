<?php

use App\Models\TourPackagePage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $page = TourPackagePage::query()->where('slug', 'series-departures-2026')->first();

        if ($page === null) {
            return;
        }

        $packageData = $page->package_data ?? [];
        $series = $packageData['series'] ?? [];
        $existingNames = array_column($series, 'name');
        $updated = false;

        foreach ($this->packages() as $package) {
            if (in_array($package['name'], $existingNames, true)) {
                continue;
            }

            $series[] = $package;
            $existingNames[] = $package['name'];
            $updated = true;
        }

        if ($updated) {
            $packageData['series'] = array_values($series);
            $page->package_data = $packageData;
            $page->save();
        }
    }

    public function down(): void
    {
        $page = TourPackagePage::query()->where('slug', 'series-departures-2026')->first();

        if ($page === null) {
            return;
        }

        $packageData = $page->package_data ?? [];
        $series = $packageData['series'] ?? [];
        $packages = $this->packages();
        $updatedSeries = array_values(array_filter(
            $series,
            fn (array $destination): bool => ! in_array($destination, $packages, true),
        ));

        if (count($updatedSeries) !== count($series)) {
            $packageData['series'] = $updatedSeries;
            $page->package_data = $packageData;
            $page->save();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function packages(): array
    {
        return [
            [
                'name' => 'Kunming – Dali – Lijiang – Shangri-La',
                'country' => 'China',
                'flag' => 'images/series/flag-cn.png',
                'tagline' => 'A Yunnan journey through ancient towns, lakes and mountain scenery.',
                'intro' => 'Explore Dali, Shangri-La, Lijiang and Kunming with mosque visits, halal meals and iconic Yunnan landscapes.',
                'advantages' => [
                    ['icon' => '🏞️', 'title' => 'Dali & Erhai Lake', 'body' => 'Visit Dali Santorini, Erhai Lake and Dali Ancient City.'],
                    ['icon' => '🏔️', 'title' => 'Shangri-La Highlands', 'body' => 'Discover Napahai Wetland, Dukezong Ancient Town and Guishan Park.'],
                    ['icon' => '❄️', 'title' => 'Lijiang Mountain Views', 'body' => 'See Yulong Snow Mountain, Blue Moon Valley and the Impression Lijiang Show.'],
                    ['icon' => '🕌', 'title' => 'Muslim-friendly Travel', 'body' => 'Enjoy full-board halal meals and daily mosque visits.'],
                    ['icon' => '🚄', 'title' => 'Yunnan by Bullet Train', 'body' => 'Travel between Lijiang and Kunming by high-speed train.'],
                    ['icon' => '🪨', 'title' => 'Stone Forest', 'body' => 'Explore Shilin Stone Forest and Kunming Old Street.'],
                ],
                'gallery' => [
                    ['image' => 'images/series/kunming-1.jpg', 'alt' => 'Green Lake Park in Kunming', 'caption' => 'Kunming'],
                    ['image' => 'images/series/kunming-2.jpg', 'alt' => 'Stone Forest in Yunnan', 'caption' => 'Stone Forest'],
                    ['image' => 'images/series/kunming-3.jpg', 'alt' => 'Yulong Snow Mountain near Lijiang', 'caption' => 'Yulong Snow Mountain'],
                    ['image' => 'images/series/kunming-4.jpg', 'alt' => 'Lijiang Ancient Town at dusk', 'caption' => 'Lijiang Ancient Town'],
                    ['image' => 'images/series/kunming-5.jpg', 'alt' => 'Dian Lake in Kunming', 'caption' => 'Dian Lake'],
                ],
                'package_details' => [
                    'duration' => '9D7N',
                    'headline' => 'Kunming – Dali – Lijiang – Shangri-La',
                    'travel_dates' => '',
                    'price_from' => 'Promo RM3,990 · Normal RM4,599',
                    'tour_code' => '9MKMG-J',
                    'note' => 'Promotional price RM3,990; normal price RM4,599.',
                    'itinerary_note' => '',
                    'departures' => [],
                    'itinerary' => [
                        ['day' => 'DAY 1', 'title' => 'Kuala Lumpur → Kunming', 'places' => 'Flight OD670 (21:50–01:35). Arrive in Kunming and check in to the hotel.', 'meals' => 'MOB', 'transfer' => 'Flight'],
                        ['day' => 'DAY 2', 'title' => 'Kunming → Dali', 'places' => 'Visit Erhai Lake (Santorini of China), Dali Ancient City and Huguo Road. Visit Xiaoguanyi Mosque.', 'meals' => 'B, L, D', 'transfer' => 'Coach'],
                        ['day' => 'DAY 3', 'title' => 'Dali', 'places' => 'Visit Erhai Ecological Corridor, Xizhou Ancient Town and Kelizhuang Mosque.', 'meals' => 'B, L, D', 'transfer' => 'Coach'],
                        ['day' => 'DAY 4', 'title' => 'Dali → Shangri-La', 'places' => 'Visit Muslim Village, Napahai Wetland, Dukezong Ancient Town and Guishan Park. Visit Eryuan Mosque.', 'meals' => 'B, L, D', 'transfer' => 'Coach'],
                        ['day' => 'DAY 5', 'title' => 'Shangri-La → Lijiang', 'places' => 'Visit Tiger Leaping Gorge, Lijiang Ancient Town and Sifang Street. Visit Beimenpo Mosque.', 'meals' => 'B, L, D', 'transfer' => 'Coach'],
                        ['day' => 'DAY 6', 'title' => 'Lijiang', 'places' => 'Visit Yulong Snow Mountain, Blue Moon Valley, Impression Lijiang Show and Black Dragon Pool Park.', 'meals' => 'B, L, D', 'transfer' => 'Coach'],
                        ['day' => 'DAY 7', 'title' => 'Lijiang → Kunming', 'places' => 'Travel by high-speed bullet train. Visit Dounan Flower Market and Chenggong Mosque.', 'meals' => 'B, L, D', 'transfer' => 'High-speed bullet train'],
                        ['day' => 'DAY 8', 'title' => 'Kunming → Shilin → Kuala Lumpur', 'places' => 'Visit Shilin Stone Forest, Golden Horse & Jade Rooster Archways, Kunming Old Street and Nanping Pedestrian Street. Visit Xishancun Mosque.', 'meals' => 'B, L', 'transfer' => 'Coach and airport transfer'],
                        ['day' => 'DAY 9', 'title' => 'Arrive in Kuala Lumpur', 'places' => 'Flight OD671 (02:35–06:35). Arrive in Kuala Lumpur and end of tour.', 'meals' => 'MOB', 'transfer' => 'Flight'],
                    ],
                    'includes' => [
                        'Return flight ticket',
                        '20kg baggage',
                        '4-star hotels',
                        'Air-conditioned transport',
                        'English-speaking guide',
                        'Entrance tickets',
                        'Full-board halal meals',
                        'Daily mineral water',
                        'Local guide tips',
                        '1 free oxygen cylinder',
                    ],
                    'excludes' => [
                        'Travel insurance',
                        'Winter jacket rental',
                        'Personal expenses',
                        'Peak season surcharge',
                    ],
                    'outbound' => ['flight' => 'OD670', 'route' => 'Kuala Lumpur (KUL) → Kunming (KMG)', 'time' => '21:50–01:35', 'duration' => ''],
                    'return' => ['flight' => 'OD671', 'route' => 'Kunming (KMG) → Kuala Lumpur (KUL)', 'time' => '02:35–06:35', 'duration' => ''],
                    'hotels' => [
                        ['name' => 'Huishang Hotel', 'room' => '', 'location' => 'Kunming'],
                        ['name' => 'Haicheng Smart Resort Hotel', 'room' => '', 'location' => 'Dali'],
                        ['name' => 'Lijiang Kailiya Hotel', 'room' => '', 'location' => 'Lijiang'],
                        ['name' => 'Shangri-La Haofeng', 'room' => '', 'location' => 'Shangri-La'],
                    ],
                ],
            ],
            [
                'name' => 'Beijing',
                'country' => 'China',
                'flag' => 'images/series/flag-cn.png',
                'tagline' => 'Imperial landmarks, the Great Wall and Universal Studios.',
                'intro' => 'Discover Beijing’s historic sights, Hutong culture and Universal Studio Beijing, with mosque visits and halal meals.',
                'advantages' => [
                    ['icon' => '🏯', 'title' => 'Imperial Beijing', 'body' => 'Explore Tiananmen Square and the Forbidden City.'],
                    ['icon' => '🧱', 'title' => 'Juyongguan Great Wall', 'body' => 'Visit one of Beijing’s famous Great Wall sections.'],
                    ['icon' => '🎢', 'title' => 'Universal Studio Beijing', 'body' => 'Enjoy a full day at Universal Studio Beijing with admission included.'],
                    ['icon' => '🕌', 'title' => 'Mosques & Halal Meals', 'body' => 'The itinerary includes mosque visits and halal meals.'],
                ],
                'gallery' => [],
                'package_details' => [
                    'duration' => '5D4N',
                    'headline' => 'New Beijing Classic Tour with Universal Studio Beijing',
                    'travel_dates' => '',
                    'price_from' => 'RM 5,990 (Promo)',
                    'tour_code' => '',
                    'note' => 'Universal Studio Beijing admission ticket is included.',
                    'itinerary_note' => '',
                    'departures' => [],
                    'itinerary' => [
                        ['day' => 'DAY 1', 'title' => 'Kuala Lumpur → Beijing', 'places' => 'Arrive in Beijing. Visit Tiananmen Square, the Forbidden City, Songyuli Mosque and Hongqiao Pearl Market.', 'meals' => 'MOB, L, D', 'transfer' => 'Airport transfer'],
                        ['day' => 'DAY 2', 'title' => 'Hutong & Temple of Heaven', 'places' => 'Visit Jade Gallery, take a trishaw ride at Hutong, visit Faoyuan Mosque and Olympic Village. Beijing Acrobatic Show is optional.', 'meals' => 'B, L, D', 'transfer' => 'Coach'],
                        ['day' => 'DAY 3', 'title' => 'Universal Studio Beijing', 'places' => 'Spend a full day at Universal Studio Beijing. Entrance ticket included.', 'meals' => 'B', 'transfer' => 'Coach'],
                        ['day' => 'DAY 4', 'title' => 'Juyongguan Great Wall', 'places' => 'Visit Baoshutang and Juyongguan Great Wall. In winter, visit a ski resort; in non-winter months, visit 798 Art Zone and Qianmen Street.', 'meals' => 'B, L', 'transfer' => 'Coach'],
                        ['day' => 'DAY 5', 'title' => 'Beijing → Kuala Lumpur', 'places' => 'Visit Niujie Mosque & Street, transfer to the airport and return to Kuala Lumpur.', 'meals' => 'B, MOB', 'transfer' => 'Airport transfer and return flight'],
                    ],
                    'includes' => [
                        'Return flight ticket',
                        '23kg baggage',
                        '4 nights hotel',
                        'Air-conditioned transport',
                        'English-speaking guide',
                        'Attraction entrance tickets',
                        'Mineral water',
                        'Halal meals',
                        'Tour guide tips',
                    ],
                    'excludes' => [
                        'Travel insurance',
                        'Personal expenses',
                        'Single room supplement',
                    ],
                    'outbound' => ['flight' => 'MU796', 'route' => 'Kuala Lumpur (KUL) → Beijing Daxing (PKX)', 'time' => '01:25–07:50', 'duration' => ''],
                    'return' => ['flight' => 'MU795', 'route' => 'Beijing Daxing (PKX) → Kuala Lumpur (KUL)', 'time' => '18:20–00:25+1', 'duration' => ''],
                    'hotels' => [
                        ['name' => 'Derreal Hotel or similar', 'room' => '4-star', 'location' => 'Beijing'],
                        ['name' => 'Kaiyuan Hotel', 'room' => '4-star', 'location' => 'Beijing'],
                    ],
                ],
            ],
        ];
    }
};
