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
            $name = $destination['name'] ?? null;

            if (! in_array($name, ['Seoul', 'Da Nang'], true) || ! empty($destination['package_details'])) {
                continue;
            }

            $series[$index]['package_details'] = $this->packageDetails($name);
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
            $name = $destination['name'] ?? null;

            if (! in_array($name, ['Seoul', 'Da Nang'], true) || ($destination['package_details'] ?? null) !== $this->packageDetails($name)) {
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
    private function packageDetails(string $destination): array
    {
        if ($destination === 'Seoul') {
            return [
                'duration' => '6 days · 4 nights',
                'headline' => 'Seoul tour package',
                'travel_dates' => '6 – 11 Sep 2026',
                'price_from' => 'From RM 3,850',
                'tour_code' => '6MICN',
                'note' => 'All-in package with return flights via Batik Air. Prices and seat availability are based on the package information provided.',
                'departures' => [
                    ['date' => '6 – 11 Sep 2026', 'price' => 'RM 3,850', 'availability' => '15 seats left'],
                ],
                'itinerary' => [
                    ['day' => 'DAY 1', 'title' => 'KLIA → Incheon', 'places' => 'Gather at KLIA, meet your tour leader and depart from Kuala Lumpur to Incheon.', 'meals' => '', 'transfer' => 'flight'],
                    ['day' => 'DAY 2', 'title' => 'Arrive in Korea', 'places' => 'Meet your guide, visit Nami Island, enjoy a rail bike experience, then check in and rest.', 'meals' => '', 'transfer' => 'private vehicle'],
                    ['day' => 'DAY 3', 'title' => 'Everland & themed streets', 'places' => 'Explore Everland Theme Park, Songwol-dong Fairy Tale Village and Japanese Street.', 'meals' => '', 'transfer' => 'private vehicle'],
                    ['day' => 'DAY 4', 'title' => 'Seoul culture & city views', 'places' => 'Visit Kimchi Museum, enjoy a hanbok experience and DIY kimchi, visit a ginseng shop, Hongdae Street, N Seoul Tower and Itaewon Street.', 'meals' => '', 'transfer' => 'private vehicle'],
                    ['day' => 'DAY 5', 'title' => 'Palaces & shopping', 'places' => 'Visit cosmetic and Red Pine shops, National Folk Museum, Gyeongbokgung Palace and Myeongdong Street.', 'meals' => '', 'transfer' => 'private vehicle'],
                    ['day' => 'DAY 6', 'title' => 'Incheon → KLIA', 'places' => 'Check out at 3:00 AM, transfer to Incheon Airport, fly home and arrive at KLIA. Tour ends.', 'meals' => '', 'transfer' => 'private vehicle and return flight'],
                ],
                'includes' => [
                    'Return Batik Air flight tickets',
                    '20 kg checked baggage and meals on board',
                    '3-star hotel accommodation',
                    'Private transportation',
                    'Full-board halal meals',
                    'English-speaking guide and Malay-speaking tour leader',
                    'Entrance tickets stated in the itinerary',
                ],
                'excludes' => ['K-ETA application fee', 'Travel insurance', 'Personal expenses', 'Optional activities', 'Items not stated under package inclusions'],
                'outbound' => ['flight' => 'OD820', 'route' => 'Kuala Lumpur (KUL) → Incheon (ICN)', 'time' => '22:00 – 05:50', 'duration' => '6 hours 50 minutes'],
                'return' => ['flight' => 'OD821', 'route' => 'Incheon (ICN) → Kuala Lumpur (KUL)', 'time' => '06:50 – 12:30', 'duration' => '6 hours 40 minutes'],
                'hotel' => [
                    'name' => 'Bernoui and Lanling Pavillion Hotel',
                    'room' => 'Twin-share',
                    'location' => 'Kunming, China (as listed in the supplied notes)',
                    'note' => 'The supplied notes list Kunming, China for these stays. Please confirm the Seoul hotel details with the tour operator before booking.',
                ],
                'essentials' => [
                    'visa' => 'K-ETA for Malaysians · 10,000 KRW',
                    'arrival_card' => 'Free for travellers under 16 and over 65',
                    'currency' => 'Korean Won (KRW)',
                    'time' => 'GMT +9',
                    'language' => 'Korean and English',
                ],
                'weather' => [
                    ['season' => 'Winter · Dec–Feb', 'temperature' => '−7°C to 5°C', 'description' => 'Cold winter season'],
                    ['season' => 'Spring · Mar–May', 'temperature' => '3°C to 25°C', 'description' => 'Mild spring season'],
                    ['season' => 'Summer · Jun–Aug', 'temperature' => '20°C to 35°C', 'description' => 'Warm summer season'],
                    ['season' => 'Autumn · Sep–Nov', 'temperature' => '15°C to 18°C', 'description' => 'Cool autumn season'],
                ],
            ];
        }

        return [
            'duration' => '5 days · 4 nights',
            'headline' => 'Discover Da Nang, Hoi An & Ba Na Hills.',
            'travel_dates' => 'September–December 2026',
            'price_from' => 'From RM 1,890',
            'tour_code' => '5MDAD-S',
            'note' => 'All-in package via Batik Air. Dates, prices and seat availability are shown as supplied.',
            'departures' => [
                ['date' => '17 Sep 2026', 'price' => 'RM 1,890', 'availability' => 'Fully booked'],
                ['date' => '18 Oct 2026', 'price' => 'RM 1,890', 'availability' => 'Fully booked'],
                ['date' => '14 Nov 2026', 'price' => 'RM 1,890', 'availability' => 'Fully booked'],
                ['date' => '21 Nov 2026', 'price' => 'RM 1,890', 'availability' => '15 seats left'],
                ['date' => '24 Nov 2026', 'price' => 'RM 1,890', 'availability' => '15 seats left'],
                ['date' => '12 Dec 2026', 'price' => 'RM 2,190', 'availability' => '15 seats left'],
                ['date' => '22 Dec 2026', 'price' => 'RM 2,190', 'availability' => '15 seats left'],
                ['date' => '26 Dec 2026', 'price' => 'RM 2,190', 'availability' => '15 seats left'],
                ['date' => '14 Mar 2026', 'price' => 'RM 2,190', 'availability' => '15 seats left'],
                ['date' => '20 Mar 2026', 'price' => 'RM 2,190', 'availability' => '15 seats left'],
            ],
            'itinerary' => [
                ['day' => 'DAY 1', 'title' => 'Kuala Lumpur → Da Nang', 'places' => 'Fly from Malaysia to Da Nang. Visit Son Tra Peninsula, Linh Ung Pagoda and the Marble Mountains.', 'meals' => 'MOB and dinner', 'transfer' => 'flight and private vehicle'],
                ['day' => 'DAY 2', 'title' => 'Ba Na Hills → Da Nang', 'places' => 'Visit Ba Na Hills, French Village, Golden Bridge, Fantasy Park, Jurassic Park and Vietnam Wax Museum.', 'meals' => 'breakfast, lunch and dinner', 'transfer' => 'private vehicle'],
                ['day' => 'DAY 3', 'title' => 'Da Nang → Hoi An', 'places' => 'Visit Cam Thanh Coconut Village and Hoi An Ancient Town, including Tan Ky Old House, Japanese Bridge, Fukian Assembly Hall and Hoi An Market.', 'meals' => 'breakfast, lunch and dinner', 'transfer' => 'private vehicle'],
                ['day' => 'DAY 4', 'title' => 'Da Nang shopping tour', 'places' => 'Visit Han Market and enjoy shopping and leisure time.', 'meals' => 'breakfast and dinner', 'transfer' => 'private vehicle'],
                ['day' => 'DAY 5', 'title' => 'Da Nang → Kuala Lumpur', 'places' => 'Have breakfast at the hotel, transfer to Da Nang International Airport and fly from Da Nang to Malaysia. End of services.', 'meals' => 'breakfast', 'transfer' => 'private vehicle and return flight'],
            ],
            'includes' => [
                'Return Batik Air flight tickets',
                '20 kg baggage and meals on board',
                '3-star hotel accommodation',
                'Private transportation',
                'Full-board halal meals',
                'English-speaking tour guide',
                'Malay-speaking tour leader',
                'Entrance tickets stated in the itinerary',
            ],
            'excludes' => ['Visa, if applicable', 'Travel insurance', 'Personal expenses', 'Optional activities'],
            'outbound' => ['flight' => 'OD502', 'route' => 'Kuala Lumpur (KUL) → Da Nang (DAD)', 'time' => '10:30 – 12:05', 'duration' => '2 hours 35 minutes'],
            'return' => ['flight' => 'OD503', 'route' => 'Da Nang (DAD) → Kuala Lumpur (KUL)', 'time' => '12:50 – 16:40', 'duration' => '2 hours 50 minutes'],
            'hotel' => ['name' => 'Chill Palace Hotel', 'room' => 'Superior Room · twin-share', 'location' => 'Da Nang, Vietnam', 'note' => ''],
            'essentials' => [
                'visa' => 'Free for Malaysian citizens',
                'arrival_card' => 'Required before arrival',
                'currency' => 'Vietnam Dong (VND)',
                'time' => '1 hour behind Malaysia',
                'language' => 'Vietnamese',
            ],
            'weather' => [
                ['season' => 'Jan–Mar', 'temperature' => '28–32°C', 'description' => 'Sunny season'],
                ['season' => 'Apr–Jun', 'temperature' => '25–30°C', 'description' => 'Rainy season'],
                ['season' => 'Jul–Dec', 'temperature' => '25–32°C', 'description' => 'Cloudy season'],
            ],
        ];
    }
};
