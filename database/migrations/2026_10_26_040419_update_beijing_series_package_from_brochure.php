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
        $index = array_search('Beijing', array_column($series, 'name'), true);

        if ($index === false) {
            return;
        }

        $series[$index] = [
            ...$this->beijing(),
            'flag' => $series[$index]['flag'] ?? 'images/series/flag-cn.png',
            'gallery' => $series[$index]['gallery'] ?? [],
        ];

        $packageData['series'] = array_values($series);
        $page->package_data = $packageData;
        $page->save();
    }

    /**
     * The brochure content replaces admin-edited text, so it is not restored on rollback.
     */
    public function down(): void
    {
        //
    }

    /**
     * Content from the "New Beijing Classic Tour with Universal Studio Beijing" brochure (doc date 18 Aug 2026).
     *
     * @return array<string, mixed>
     */
    private function beijing(): array
    {
        return [
            'name' => 'Beijing',
            'country' => 'China',
            'tagline' => '5D4N · Culture, heritage & fun with Universal Studio Beijing.',
            'intro' => 'Our New Beijing Classic Tour covers Tiananmen Square, the Forbidden City, a trishaw ride through Old Beijing, the Juyongguan Great Wall and a full day at Universal Studio Beijing, with mosque stops for prayer and halal meals throughout.',
            'advantages' => [
                ['icon' => '✈️', 'title' => 'Flight, Baggage & MOB', 'body' => 'Return China Eastern flights with 23kg baggage and meals on board.'],
                ['icon' => '🏨', 'title' => '4-Star Hotel', 'body' => '4 nights at Beijing Derreal Hotel or Kaiyuan Hotel, with a water bidet in the room.'],
                ['icon' => '🕌', 'title' => 'Mosque Stops for Prayer', 'body' => 'Pray at Songyuli, Faoyuan and Niujie mosques, with halal meals every day.'],
                ['icon' => '🛺', 'title' => 'Trishaw at Old Beijing', 'body' => 'Ride a trishaw through the Hutong lanes of Old Beijing.'],
                ['icon' => '🎢', 'title' => 'Universal Studio Beijing', 'body' => 'A full day of fun with the standard entrance ticket included.'],
                ['icon' => '⛷️', 'title' => 'Ski Resort (Winter Only)', 'body' => 'Ski resort entrance is included from the first week of December.'],
            ],
            'package_details' => [
                'duration' => '5D4N',
                'headline' => 'New Beijing Classic Tour with Universal Studio Beijing',
                'travel_dates' => 'October 2026 – March 2027',
                'price_from' => 'Promo RM 5,990 · Buy 1 Free 1',
                'tour_code' => '5MPKX',
                'note' => 'MATTA Fair promo (4–6 Sept 2026, MITEC): Buy 1 Free 1. December departures add RM 200 per person. Contact us for a price quotation: +6016-550 0287 / +6017-388 6708.',
                'itinerary_note' => 'Ski Resort is included from the first week of December (entrance ticket included, ski equipment excluded). Before the winter season it is replaced with 798 Art Zone and Qianmen Da Jie Street.',
                'departures' => [
                    ['date' => 'October 2026: 31', 'price' => 'RM 5,990', 'availability' => 'Buy 1 Free 1'],
                    ['date' => 'November 2026: 7, 14, 21, 28', 'price' => 'RM 5,990', 'availability' => 'Buy 1 Free 1'],
                    ['date' => 'December 2026: 12, 28', 'price' => 'RM 5,990 + RM 200/pax', 'availability' => 'Buy 1 Free 1'],
                    ['date' => 'January 2027: 9, 16, 23', 'price' => 'RM 5,990', 'availability' => 'Buy 1 Free 1'],
                    ['date' => 'March 2027: 20', 'price' => 'RM 5,990', 'availability' => 'Buy 1 Free 1'],
                ],
                'itinerary' => [
                    ['day' => 'DAY 1', 'title' => 'Kuala Lumpur → Beijing', 'places' => 'Depart Malaysia on MU796 (01:25–07:50). Arrive at Beijing Daxing (PKX) and meet your guide. Visit Tiananmen Square and the Forbidden City, lunch at a local halal restaurant, visit Songyuli Mosque, then learn about Chinese herbal medicine before shopping at Hongqiao Pearl Market.', 'meals' => 'MOB, L, D', 'transfer' => 'Flight and coach'],
                    ['day' => 'DAY 2', 'title' => 'Hutong & Temple of Heaven', 'places' => 'Breakfast at a local halal restaurant. Visit the Jade Gallery, take a trishaw ride through the Hutong old town (Old Beijing), have lunch and pray at Faoyuan Mosque, then visit the Olympic Village (Bird’s Nest). Beijing Acrobatic Show is optional. Dinner at a halal restaurant.', 'meals' => 'B, L, D', 'transfer' => 'Coach'],
                    ['day' => 'DAY 3', 'title' => 'Universal Studio Beijing', 'places' => 'Breakfast at a local halal restaurant, then a full day of fun at Universal Studio Beijing (standard entrance ticket provided). Pick-up and return to the hotel.', 'meals' => 'B', 'transfer' => 'Coach'],
                    ['day' => 'DAY 4', 'title' => 'Great Wall – 798 Art – Qianmen', 'places' => 'Breakfast at a local halal restaurant. Visit Baoshutang and explore the Juyongguan Great Wall. Ski Resort from the first week of December (entrance included, ski equipment excluded); before winter, visit 798 Art Zone and Qianmen Da Jie Street instead.', 'meals' => 'B, L', 'transfer' => 'Coach'],
                    ['day' => 'DAY 5', 'title' => 'Beijing → Kuala Lumpur', 'places' => 'Breakfast at a local halal restaurant and check out before 12:00 PM. Stop at Niujie Mosque & Street to pray and try local Muslim snacks (lunch at own expense), then transfer to Daxing International Airport for MU795 (ETD 18:20).', 'meals' => 'B, MOB', 'transfer' => 'Coach and flight'],
                    ['day' => 'DAY 6', 'title' => 'Arrive in Kuala Lumpur', 'places' => 'Arrive at Kuala Lumpur International Airport (00:25). End of tour.', 'meals' => '', 'transfer' => ''],
                ],
                'includes' => [
                    'Return flight ticket + 23kg baggage + MOB',
                    '4 nights at Derreal Hotel or similar (water bidet in the room)',
                    'Transport in air-conditioned vehicles',
                    'English-speaking local guide',
                    'Entrance fees to sights as mentioned',
                    'Universal Studio Beijing standard entrance ticket',
                    '0.5 litre mineral water per person per day',
                    'Halal meals: 4 breakfasts, 4 lunches, 3 dinners',
                    'Tipping for the local guide',
                ],
                'excludes' => [
                    'Travel insurance',
                    'Personal expenses',
                    'Single room supplement',
                    'Ski equipment',
                    'Beijing Acrobatic Show (optional ticket)',
                ],
                'outbound' => ['flight' => 'MU796', 'route' => 'Kuala Lumpur (KUL) → Beijing Daxing (PKX)', 'time' => '01:25 – 07:50', 'duration' => ''],
                'return' => ['flight' => 'MU795', 'route' => 'Beijing Daxing (PKX) → Kuala Lumpur (KUL)', 'time' => '18:20 – 00:25 (+1)', 'duration' => ''],
                'hotels' => [
                    ['name' => 'Beijing Derreal Hotel or Kaiyuan Hotel', 'room' => '4-star', 'location' => 'Beijing', 'note' => 'Water bidet in the room.'],
                ],
                'essentials' => [
                    'visa' => '',
                    'arrival_card' => '',
                    'currency' => '',
                    'time' => '',
                    'language' => '',
                ],
                'weather' => [
                    ['season' => 'Spring · Mar–May', 'temperature' => '5°C to 25°C', 'description' => 'Spring'],
                    ['season' => 'Summer · Jun–Aug', 'temperature' => '25°C to 35°C', 'description' => 'Summer'],
                    ['season' => 'Autumn · Sep–Nov', 'temperature' => '10°C to 25°C', 'description' => 'Autumn'],
                    ['season' => 'Winter · Dec–Feb', 'temperature' => '−10°C to 5°C', 'description' => 'Winter'],
                ],
            ],
        ];
    }
};
