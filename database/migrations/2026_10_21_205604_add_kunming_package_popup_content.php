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
        $updated = false;

        foreach ($series as $index => $destination) {
            if (($destination['name'] ?? null) !== 'Kunming' || ! empty($destination['package_details'])) {
                continue;
            }

            $series[$index]['package_details'] = $this->packageDetails();
            $updated = true;
        }

        if ($updated) {
            $packageData['series'] = $series;
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
        $updated = false;

        foreach ($series as $index => $destination) {
            if (($destination['name'] ?? null) !== 'Kunming' || ($destination['package_details'] ?? null) !== $this->packageDetails()) {
                continue;
            }

            unset($series[$index]['package_details']);
            $updated = true;
        }

        if ($updated) {
            $packageData['series'] = array_values($series);
            $page->package_data = $packageData;
            $page->save();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function packageDetails(): array
    {
        return [
            'duration' => '9 days · 7 nights',
            'headline' => 'Kunming & Yunnan tour package',
            'travel_dates' => '20 Oct – 12 Dec 2026',
            'price_from' => 'From RM 4,350',
            'tour_code' => '9MKKMGJ-S',
            'note' => 'All-in package via Batik Air. Prices and seat availability are based on the package information provided.',
            'itinerary_note' => 'Please confirm Days 8–9 with the tour operator: the supplied information lists an airport transfer and an overnight in Kunming on Day 8, then departure on Day 9.',
            'departures' => [
                ['date' => '20 Oct 2026', 'price' => 'RM 4,350', 'availability' => '15 seats left'],
                ['date' => '25 Oct 2026', 'price' => 'RM 4,550', 'availability' => '15 seats left'],
                ['date' => '27 Oct 2026', 'price' => 'RM 4,350', 'availability' => '15 seats left'],
                ['date' => '30 Oct 2026', 'price' => 'RM 4,550', 'availability' => '15 seats left'],
                ['date' => '01 Nov 2026', 'price' => 'RM 4,650', 'availability' => '15 seats left'],
                ['date' => '21 Nov 2026', 'price' => 'RM 4,550', 'availability' => '15 seats left'],
                ['date' => '12 Dec 2026', 'price' => 'RM 4,850', 'availability' => '15 seats left'],
            ],
            'itinerary' => [
                ['day' => 'DAY 1', 'title' => 'KLIA → Kunming', 'places' => 'Gather at KLIA, meet your tour leader and depart from Kuala Lumpur to Kunming.', 'meals' => '', 'transfer' => 'flight'],
                ['day' => 'DAY 2', 'title' => 'Kunming → Dali', 'places' => 'Arrive in Kunming and meet your guide. Check in and rest, then have breakfast and check out. Transfer by coach to Dali (about 4.5 hours), visit Santorini of Dali and Dali Ancient Town, then stay overnight in Dali.', 'meals' => '', 'transfer' => 'coach'],
                ['day' => 'DAY 3', 'title' => 'Dali → Lijiang', 'places' => 'Visit Erhai Lake and Xizhou Ancient Town. Transfer by coach to Lijiang (about 2 hours), visit Shaxi Ancient Town and stay overnight in Lijiang.', 'meals' => '', 'transfer' => 'coach'],
                ['day' => 'DAY 4', 'title' => 'Lijiang → Shangri-La', 'places' => 'Travel to Shangri-La (about 2.5 hours). Visit Tiger Leaping Gorge, Dukezong Ancient Town and Guishan Park. Overnight in Shangri-La.', 'meals' => '', 'transfer' => 'coach'],
                ['day' => 'DAY 5', 'title' => 'Shangri-La → Lijiang', 'places' => 'Return to Lijiang, visit Tinghua Valley and walk around Lijiang Ancient City. Overnight in Lijiang.', 'meals' => '', 'transfer' => 'coach'],
                ['day' => 'DAY 6', 'title' => 'Lijiang & Jade Snow Mountain', 'places' => 'Visit Jade Snow Mountain, Glacier Park and Blue Moon Valley, watch the Impression Lijiang Show, and visit Yushui Village. Overnight in Lijiang.', 'meals' => '', 'transfer' => 'private vehicle'],
                ['day' => 'DAY 7', 'title' => 'Lijiang → Kunming', 'places' => 'Take the bullet train to Kunming, visit Stone Forest and stay overnight in Kunming.', 'meals' => '', 'transfer' => 'bullet train and private vehicle'],
                ['day' => 'DAY 8', 'title' => 'Kunming', 'places' => 'The supplied itinerary lists Luosiwan shopping, Shuncheng Mosque and Nanping Street, followed by airport transfer. It also lists another Stone Forest visit and an overnight in Kunming.', 'meals' => '', 'transfer' => 'private vehicle'],
                ['day' => 'DAY 9', 'title' => 'Kunming → Kuala Lumpur', 'places' => 'Depart from Kunming (KMG) to Kuala Lumpur (KUL), arrive in Kuala Lumpur and end the tour.', 'meals' => '', 'transfer' => 'return flight'],
            ],
            'includes' => [
                'Return Batik Air flight tickets',
                '20 kg checked baggage and meals on board',
                '4-star hotel accommodation',
                'Private transportation',
                'Full-board halal meals',
                'English-speaking guide and Malay-speaking tour leader',
                'Entrance tickets stated in the itinerary',
                'Visa free for Malaysian citizens',
            ],
            'excludes' => [
                'Visa for other nationalities',
                'Travel insurance',
                'Personal expenses',
                'Optional activities',
                'Jade Snow Mountain winter jacket and oxygen bottle rental (80 yuan)',
            ],
            'outbound' => ['flight' => 'OD670', 'route' => 'Kuala Lumpur (KUL) → Kunming (KMG)', 'time' => '21:50 – 01:35', 'duration' => '3 hours 45 minutes'],
            'return' => ['flight' => 'OD671', 'route' => 'Kunming (KMG) → Kuala Lumpur (KUL)', 'time' => '02:35 – 06:35', 'duration' => '3 hours 45 minutes'],
            'hotels' => [
                ['name' => 'Days Inn Select Wyndham Airport', 'room' => '4-star · twin-share', 'location' => 'Day 2 · Kunming'],
                ['name' => 'Lanling Pavillion Hotel', 'room' => '4-star · twin-share', 'location' => 'Day 3 · Dali'],
                ['name' => 'Kyriad Hotel', 'room' => '4-star · twin-share', 'location' => 'Day 4 · Lijiang'],
                ['name' => 'Lanou International Hotel', 'room' => '4-star · twin-share', 'location' => 'Day 5 · Shangri-La'],
                ['name' => 'Kaison Hotel', 'room' => '4-star · twin-share', 'location' => 'Days 6–7 · Lijiang'],
                ['name' => 'Weilong Hotel', 'room' => '4-star · twin-share', 'location' => 'Day 8 · Kunming'],
            ],
            'essentials' => [
                'visa' => 'Free for Malaysian citizens',
                'arrival_card' => 'Required before arrival',
                'currency' => 'Chinese Yuan',
                'time' => 'GMT +8 · same as Malaysia',
                'language' => 'Chinese and English',
            ],
            'weather' => [
                ['season' => 'Winter · Dec–Feb', 'temperature' => '−7°C to 5°C', 'description' => 'Cold winter season'],
                ['season' => 'Spring · Mar–May', 'temperature' => '3°C to 25°C', 'description' => 'Mild spring season'],
                ['season' => 'Summer · Jun–Aug', 'temperature' => '20°C to 35°C', 'description' => 'Warm summer season'],
                ['season' => 'Autumn · Sep–Nov', 'temperature' => '15°C to 18°C', 'description' => 'Cool autumn season'],
            ],
        ];
    }
};
