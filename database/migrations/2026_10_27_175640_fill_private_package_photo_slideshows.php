<?php

use App\Models\TourPackagePage;
use Illuminate\Database\Migrations\Migration;

/**
 * Gives every private tour package a full-screen photo slideshow. Only placeholder galleries
 * (one photo or fewer, or every caption still "DISCOVER") are replaced; curated galleries are left alone.
 * Remote photos are from Unsplash, which allows free commercial use without attribution.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->galleries() as $slug => [$body, $gallery]) {
            $page = TourPackagePage::query()->where('is_custom', true)->where('slug', $slug)->first();

            if ($page === null) {
                continue;
            }

            $data = $page->package_data ?? [];

            if (! $this->isPlaceholderGallery($data['gallery'] ?? [])) {
                continue;
            }

            $data['gallery'] = array_map(static fn (array $photo): array => [
                'image' => $photo[0],
                'alt' => $photo[1],
                'eyebrow' => $photo[2],
                'title' => $photo[3],
            ], $gallery);

            if (($data['gallery_eyebrow'] ?? '') === 'DESTINATION PHOTOS' || ($data['gallery_eyebrow'] ?? '') === 'A GLIMPSE OF THE DESTINATION' || blank($data['gallery_eyebrow'] ?? null)) {
                $data['gallery_eyebrow'] = 'A GLIMPSE OF THE JOURNEY';
                $data['gallery_headline'] = "{$page->name} highlights";
                $data['gallery_body'] = $body;
            }

            $page->package_data = $data;
            $page->saveQuietly();
        }
    }

    public function down(): void
    {
        // The placeholder photos are not worth restoring.
    }

    /**
     * @param  array<int, array<string, string>>  $gallery
     */
    private function isPlaceholderGallery(array $gallery): bool
    {
        return count($gallery) <= 1
            || collect($gallery)->every(static fn (array $photo): bool => ($photo['eyebrow'] ?? '') === 'DISCOVER');
    }

    private function unsplash(string $id): string
    {
        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1800&q=85";
    }

    /**
     * Slideshow text and photos ([image, alt, eyebrow, title]) for each package.
     *
     * @return array<string, array{0: string, 1: array<int, array{0: string, 1: string, 2: string, 3: string}>}>
     */
    private function galleries(): array
    {
        $u = fn (string $id): string => $this->unsplash($id);

        return [
            'yogyakarta' => ['From Borobudur at sunrise to the temple terraces of Central Java.', [
                [$u('1566559532224-6d65e9fc0f37'), 'Stone stupas on the terraces of Borobudur Temple', 'UNESCO WORLD HERITAGE', 'Borobudur Temple'],
                [$u('1591674585153-ca78d0339b09'), 'Borobudur stupas silhouetted against the morning sky', 'BEFORE THE CROWDS', 'Sunrise at Borobudur'],
                [$u('1741552723057-9e275ea416cf'), 'Borobudur Temple rising beyond the surrounding gardens', 'CENTRAL JAVA', 'Borobudur grounds'],
            ]],
            'medan' => ['Waterfalls, Batak villages and Medan’s grand landmarks.', [
                ['images/series/medan-1.jpg', 'Sipiso-piso Waterfall plunging into a green canyon', 'NORTH SUMATRA', 'Sipiso-piso Waterfall'],
                ['images/series/medan-5.jpg', 'Traditional Batak houses with curved roofs on Samosir Island', 'BATAK CULTURE', 'Samosir Island'],
                ['images/series/medan-4.jpg', 'The Great Mosque of Medan with its black domes', 'FAITH & ARCHITECTURE', 'Great Mosque of Medan'],
                ['images/series/medan-3.jpg', 'Maimun Palace in Medan under a blue sky', 'ROYAL HERITAGE', 'Maimun Palace'],
            ]],
            'surabaya' => ['Sunrise over the Bromo Tengger Semeru volcanoes of East Java.', [
                [$u('1666887799869-4fbeda25d17f'), 'Mount Bromo surrounded by morning clouds', 'EAST JAVA', 'Mount Bromo'],
                [$u('1505993597083-3bd19fb75e57'), 'Bromo volcanic complex glowing at golden hour', 'AN EARLY START', 'Bromo sunrise viewpoint'],
            ]],
            'padang' => ['The highland town of Bukittinggi and its famous clock tower.', [
                [$u('1754615842174-7e6c8d1f0ca3'), 'Jam Gadang clock tower in the centre of Bukittinggi', 'WEST SUMATRA', 'Jam Gadang'],
                [$u('1657453725781-21c0e61e5069'), 'Close view of the Jam Gadang clock tower', 'BUKITTINGGI', 'The heart of Bukittinggi'],
            ]],
            'bangkok' => ['Riverside temples, golden spires and Bangkok’s glowing night streets.', [
                [$u('1747235156815-3b39a7097b7b'), 'Wat Arun temple rising towards the sky', 'TEMPLE OF DAWN', 'Wat Arun'],
                [$u('1591357820463-55f8f8d7d483'), 'Visitors among the porcelain-tiled spires of Wat Arun', 'UP CLOSE', 'Wat Arun’s spires'],
                [$u('1568321385520-b9252021b491'), 'White and gold temple spires in Bangkok', 'BANGKOK', 'Temple quarter'],
                [$u('1508009603885-50cf7c579365'), 'Neon signs and tuk-tuks on a Bangkok street at night', 'AFTER DARK', 'Bangkok by night'],
            ]],
            'pattaya' => ['Pattaya’s beaches, island day trips and Bangkok’s temples.', [
                [$u('1620373901514-4319a7c81cdb'), 'Pattaya Beach lined with hotels and visitors', 'THE GULF COAST', 'Pattaya Beach'],
                [$u('1475645486310-a1e31c4cbfee'), 'Pier and blue sea at Koh Larn near Pattaya', 'ISLAND DAY TRIP', 'Koh Larn'],
                [$u('1747235156815-3b39a7097b7b'), 'Wat Arun temple in Bangkok', 'BANGKOK', 'Wat Arun'],
                [$u('1508009603885-50cf7c579365'), 'Bangkok street with neon signs at night', 'CITY NIGHTS', 'Bangkok by night'],
            ]],
            'kanchanaburi' => ['The historic railway by the River Kwai, then Bangkok’s temples and markets.', [
                [$u('1624807177672-468d85ff1803'), 'Railway line running beside the River Kwai', 'HISTORY BY THE RIVER', 'River Kwai railway'],
                [$u('1591357820463-55f8f8d7d483'), 'Porcelain-tiled spires of Wat Arun in Bangkok', 'BANGKOK', 'Wat Arun'],
                [$u('1508009603885-50cf7c579365'), 'Bangkok street market lights at night', 'MARKETS & SHOPPING', 'Bangkok by night'],
            ]],
            'chiang-mai-chiang-rai' => ['Golden temples, the White Temple and Northern Thailand’s green valleys.', [
                [$u('1671188893377-ee825a53d27f'), 'The White Temple reflected in still water in Chiang Rai', 'CHIANG RAI', 'Wat Rong Khun'],
                [$u('1528181304800-259b08848526'), 'Golden Lanna temples in Chiang Mai', 'CHIANG MAI', 'Lanna temples'],
                [$u('1573822619717-605a97a3856c'), 'Ornate white spires of Wat Rong Khun', 'THE WHITE TEMPLE', 'A work of art'],
                [$u('1532925562556-266f9db734ad'), 'Misty countryside and rice fields in Northern Thailand', 'NORTHERN THAILAND', 'Mountain valleys'],
            ]],
            'maldives' => ['Overwater villas, coral lagoons and endless turquoise sea.', [
                ['images/island-honeymoon/maldives-water-villas.jpg', 'Maldives resort island and water villas on the horizon', 'ISLAND ESCAPE', 'The Maldives'],
                [$u('1688949078626-a358f500e063'), 'Aerial view of overwater villas curving across a lagoon', 'FROM ABOVE', 'Overwater villas'],
                [$u('1602002418679-43121356bf41'), 'Thatched overwater bungalows above clear water', 'STAY ON THE WATER', 'Lagoon bungalows'],
                [$u('1573843981267-be1999ff37cd'), 'Resort jetty and villas over a turquoise lagoon', 'SLOW DAYS', 'Resort lagoon'],
            ]],
            'hanoi' => ['Sapa’s rice terraces and the limestone islands of Ha Long Bay.', [
                [$u('1634951412593-b2cdca1ae519'), 'Limestone karsts rising from the water in Ha Long Bay', 'UNESCO WORLD HERITAGE', 'Ha Long Bay'],
                [$u('1758003293450-b9478f02bf48'), 'Golden rice terraces on a hillside in Sapa', 'NORTHERN HIGHLANDS', 'Sapa rice terraces'],
                [$u('1561461221-959c3f16234b'), 'Calm water among the islands of Ha Long Bay', 'BY BOAT', 'Cruising the bay'],
                [$u('1758002766412-82897c5e5429'), 'Yellow rice terraces filling a mountain valley', 'SAPA', 'Valley views'],
            ]],
            'da-nang' => ['The Golden Bridge, Dragon Bridge and a lantern-lit evening in Hoi An.', [
                ['images/series/danang-3.jpg', 'The Golden Bridge held by giant stone hands at Ba Na Hills', 'BA NA HILLS', 'The Golden Bridge'],
                ['images/series/danang-1.jpg', 'Dragon Bridge lit up at night in Da Nang', 'AFTER DARK', 'Dragon Bridge'],
                ['images/series/danang-2.jpg', 'Long sandy beach and blue sea in Da Nang', 'BY THE SEA', 'My Khe Beach'],
                ['images/series/danang-4.jpg', 'Da Nang framed by the Marble Mountains', 'LIMESTONE PEAKS', 'Marble Mountains'],
                [$u('1691927644490-e1a24b366a5e'), 'Colourful silk lanterns hanging in Hoi An', 'HOI AN', 'Lantern-lit old town'],
            ]],
            'ho-chi-minh' => ['A riverside skyline, busy streets and Saigon after dark.', [
                [$u('1583417319070-4a69db38a482'), 'Ho Chi Minh City skyline at dusk across the river', 'SAIGON', 'Riverside skyline'],
                [$u('1586595253743-578ead3c4fc6'), 'Aerial view of Ho Chi Minh City and the Saigon River', 'FROM ABOVE', 'City and river'],
                [$u('1649577867892-08811fa2b4f3'), 'Tree-lined street with traffic in central Ho Chi Minh City', 'CITY LIFE', 'Downtown streets'],
            ]],
            'hoi-an' => ['Silk lanterns, old shophouses and a glowing night market.', [
                [$u('1691927644490-e1a24b366a5e'), 'Colourful lanterns hanging from a tree in Hoi An', 'ANCIENT TOWN', 'Hoi An lanterns'],
                [$u('1639458110591-17c4cede0c4b'), 'A lantern shop filled with glowing silk lanterns', 'CRAFT & COLOUR', 'Lantern makers'],
                ['images/series/danang-5.jpg', 'Lantern-lit night market crowded with visitors', 'AFTER DARK', 'Night market'],
                [$u('1506430806163-511b73d549b8'), 'Red paper lanterns glowing at night', 'EVENING WALKS', 'Lantern-lit streets'],
            ]],
            'manila' => ['The old walled city of Intramuros and Manila’s colourful streets.', [
                [$u('1736122094944-da4deea774ab'), 'Fort Santiago gate in Intramuros with the city behind', 'INTRAMUROS', 'Fort Santiago'],
                [$u('1559566735-f1df6589d059'), 'Trike passing a colourful street mural in Intramuros', 'STREET LIFE', 'Old Manila'],
                [$u('1539249285299-1cca3d81e20b'), 'Historic walls and steps in Intramuros', 'THE WALLED CITY', 'Historic Intramuros'],
            ]],
            'boracay' => ['Powder-white sand, clear water and sunsets over the Sibuyan Sea.', [
                [$u('1553195029-754fbd369560'), 'People walking on Boracay’s White Beach with sailboats offshore', 'WHITE BEACH', 'Boracay'],
                [$u('1668090509130-62989734a38b'), 'Waves on the rocky shore of Boracay Island', 'ISLAND SHORES', 'Rocky coves'],
                [$u('1507525428034-b723cf961d3e'), 'Golden sunset over a tropical beach', 'GOLDEN HOUR', 'Island sunsets'],
            ]],
            'palawan' => ['Hidden lagoons and limestone cliffs around El Nido.', [
                [$u('1583685173048-342a162bb888'), 'Aerial view of the Big Lagoon in El Nido', 'EL NIDO', 'Big Lagoon'],
                [$u('1654846417877-9aa89d4b8365'), 'Kayaks in a turquoise lagoon between limestone cliffs', 'ISLAND HOPPING', 'Lagoon kayaking'],
                [$u('1771533680002-5210c217a16f'), 'Tropical cove with limestone cliffs and turquoise water', 'SECRET COVES', 'Hidden beaches'],
                [$u('1771533679924-8495042032a2'), 'Lush island with rocky cliffs and a white sand beach', 'PALAWAN', 'Island scenery'],
                [$u('1518509562904-e7ef99cdcc86'), 'Clear lagoon and sandy beach beneath green islands', 'CRYSTAL WATER', 'Lagoon views'],
            ]],
            'cebu' => ['Turquoise waterfalls, a hilltop shrine and island sunsets.', [
                [$u('1586263426392-3b3e0748f618'), 'Kawasan Falls cascading into a turquoise pool', 'BADIAN', 'Kawasan Falls'],
                [$u('1581521894817-f7cb8bedd905'), 'Simala Shrine with its castle-like towers', 'FAITH & ARCHITECTURE', 'Simala Shrine'],
                [$u('1738745056034-c0ce369d21f1'), 'Green mountain range and forest in Cebu', 'INTO THE HILLS', 'Cebu highlands'],
                [$u('1738745056026-87efb5f6ebc8'), 'Colourful sunset over the sea in Cebu', 'GOLDEN HOUR', 'Cebu sunsets'],
            ]],
            'krabi' => ['Longtail boats, limestone cliffs and Andaman Sea sunsets.', [
                ['images/island-honeymoon/krabi-longtail-boats.jpg', 'Longtail boats on a white sand beach in Krabi', 'ANDAMAN COAST', 'Krabi beaches'],
                [$u('1519451241324-20b4ea2c4220'), 'Longtail boat between towering limestone cliffs', 'SOUTHERN THAILAND', 'Limestone lagoons'],
                [$u('1507525428034-b723cf961d3e'), 'Sunset over a tropical beach', 'GOLDEN HOUR', 'Andaman sunsets'],
            ]],
            'lombok' => ['Lombok’s coastline and the turquoise waters of the Gili Islands.', [
                ['images/island-honeymoon/lombok-bay.jpg', 'Green hills and a curving bay on the Lombok coast', 'LOMBOK', 'Lombok’s coast'],
                [$u('1619681216575-d6b3964fc278'), 'Aerial view of a Gili island ringed by turquoise reef', 'GILI ISLANDS', 'Island from above'],
                [$u('1583022846753-83a4eba54ac1'), 'Boats anchored in clear water off Gili Trawangan', 'GILI TRAWANGAN', 'Turquoise shallows'],
                [$u('1718357168158-d2dc91212786'), 'Sea turtle swimming over a coral reef', 'UNDER THE SEA', 'Snorkelling with turtles'],
                [$u('1609980431579-9c28eb579400'), 'Two people silhouetted on the beach at sunset', 'GOLDEN HOUR', 'Gili sunsets'],
            ]],
        ];
    }
};
