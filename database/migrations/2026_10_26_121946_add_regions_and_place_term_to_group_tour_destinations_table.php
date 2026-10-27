<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Each destination page gets its own regions and its own word for the places it lists
 * ("state" for Malaysia, "destination" elsewhere), then Vietnam, Thailand and Indonesia
 * get pages of their own, linked from their Group Tours cards.
 */
return new class extends Migration
{
    private const NEW_SLUGS = ['vietnam', 'thailand', 'indonesia'];

    public function up(): void
    {
        Schema::table('group_tour_destinations', function (Blueprint $table) {
            $table->json('regions')->nullable()->after('states_heading');
            $table->string('place_term', 30)->default('state')->after('regions');
        });

        DB::table('group_tour_destinations')->where('slug', 'malaysia')->update([
            'regions' => json_encode($this->regionList(['northern' => 'Northern', 'central' => 'Central', 'southern' => 'Southern', 'east-coast' => 'East Coast', 'borneo' => 'Borneo'])),
        ]);

        $position = (int) DB::table('group_tour_destinations')->max('position');
        $photo = static fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1600&q=85";
        $place = static fn (string $name, string $region, string $capital, string $tagline): array => ['name' => $name, 'region' => $region, 'capital' => $capital, 'tagline' => $tagline, 'image' => '', 'alt' => ''];
        $now = now();

        foreach ($this->destinations($photo, $place) as $slug => $destination) {
            if (DB::table('group_tour_destinations')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('group_tour_destinations')->insert([
                'slug' => $slug,
                'name' => $destination['name'],
                'meta_description' => "Explore group tours across {$destination['name']} with Smart Holidays by Roystar Travel & Tours.",
                'hero_eyebrow' => 'GROUP TOURS · '.strtoupper($destination['name']),
                'heading' => "Discover {$destination['name']}",
                'intro' => $destination['intro'],
                'hero_image' => $destination['hero_image'],
                'states_heading' => 'Explore the destinations',
                'regions' => json_encode($this->regionList($destination['regions'])),
                'place_term' => 'destination',
                'states' => json_encode($destination['places']),
                'region_images' => json_encode($destination['region_images']),
                'position' => ++$position,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->mapTours(static fn (array $tour): array => in_array($slug = strtolower($tour['name'] ?? ''), self::NEW_SLUGS, true) && blank($tour['link_url'] ?? '')
            ? [...$tour, 'link_url' => "/group-tours/{$slug}"]
            : $tour);
    }

    public function down(): void
    {
        $this->mapTours(static fn (array $tour): array => in_array($tour['link_url'] ?? '', array_map(static fn (string $slug): string => "/group-tours/{$slug}", self::NEW_SLUGS), true)
            ? [...$tour, 'link_url' => '']
            : $tour);

        DB::table('group_tour_destinations')->whereIn('slug', self::NEW_SLUGS)->delete();

        Schema::table('group_tour_destinations', function (Blueprint $table) {
            $table->dropColumn(['regions', 'place_term']);
        });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function destinations(Closure $photo, Closure $place): array
    {
        return [
            'vietnam' => [
                'name' => 'Vietnam',
                'intro' => 'From the limestone bays of the north to the lantern towns of the centre and the Mekong Delta in the south, choose the places you would like to explore together.',
                'hero_image' => $photo('1528127269322-539801943592'),
                'regions' => ['northern' => 'Northern', 'central' => 'Central', 'southern' => 'Southern'],
                'region_images' => ['northern' => ['image' => $photo('1528127269322-539801943592'), 'alt' => 'Boats among limestone islands in Ha Long Bay']],
                'places' => [
                    $place('Hanoi', 'northern', 'Capital city', 'Old Quarter streets, Hoan Kiem Lake and egg coffee.'),
                    $place('Ha Long Bay', 'northern', 'Quang Ninh', 'Overnight cruises among thousands of limestone islands.'),
                    $place('Sapa', 'northern', 'Lao Cai', 'Rice terraces, hill-tribe villages and cool mountain air.'),
                    $place('Ninh Binh', 'northern', '', 'Boat rides through caves and karst valleys at Trang An.'),
                    $place('Hue', 'central', 'Thua Thien Hue', 'The old imperial citadel and royal tombs on the Perfume River.'),
                    $place('Da Nang', 'central', '', 'Beaches, the Golden Bridge and the Marble Mountains.'),
                    $place('Hoi An', 'central', 'Quang Nam', 'Lantern-lit old town, tailors and riverside evenings.'),
                    $place('Ho Chi Minh City', 'southern', '', 'Busy markets, French colonial landmarks and the Cu Chi Tunnels.'),
                    $place('Mekong Delta', 'southern', '', 'Floating markets, orchards and life along the river.'),
                    $place('Phu Quoc', 'southern', 'Kien Giang', 'Island beaches, night markets and sunset views.'),
                ],
            ],
            'thailand' => [
                'name' => 'Thailand',
                'intro' => 'Temples in the north, the energy of Bangkok and island beaches in the south: choose the places you would like to explore together.',
                'hero_image' => $photo('1508009603885-50cf7c579365'),
                'regions' => ['northern' => 'Northern', 'northeast' => 'Northeast (Isan)', 'central' => 'Central', 'eastern' => 'Eastern', 'southern' => 'Southern'],
                'region_images' => ['central' => ['image' => $photo('1508009603885-50cf7c579365'), 'alt' => 'Neon signs in Bangkok at night']],
                'places' => [
                    $place('Chiang Mai', 'northern', '', 'Old city temples, night bazaars and Doi Suthep.'),
                    $place('Chiang Rai', 'northern', '', 'The White Temple and the Golden Triangle.'),
                    $place('Khao Yai', 'northeast', 'Nakhon Ratchasima', 'National park wildlife, waterfalls and vineyards.'),
                    $place('Bangkok', 'central', 'Capital city', 'Grand Palace, river markets and shopping.'),
                    $place('Ayutthaya', 'central', '', 'Temple ruins of the old Siamese capital.'),
                    $place('Kanchanaburi', 'central', '', 'The River Kwai bridge and Erawan Falls.'),
                    $place('Pattaya', 'eastern', 'Chonburi', 'Beaches, island trips and family attractions.'),
                    $place('Phuket', 'southern', '', 'Island beaches, Phang Nga Bay and old town streets.'),
                    $place('Krabi', 'southern', '', 'Limestone cliffs, island hopping and calm bays.'),
                    $place('Hat Yai', 'southern', 'Songkhla', 'Halal food, markets and an easy drive from Malaysia.'),
                ],
            ],
            'indonesia' => [
                'name' => 'Indonesia',
                'intro' => 'Volcanoes and temples on Java, Bali sunsets and the highlands of Sumatra: choose the places you would like to explore together.',
                'hero_image' => $photo('1537996194471-e657df975ab4'),
                'regions' => ['java' => 'Java', 'bali-nusa-tenggara' => 'Bali & Nusa Tenggara', 'sumatra' => 'Sumatra', 'sulawesi' => 'Sulawesi'],
                'region_images' => ['bali-nusa-tenggara' => ['image' => $photo('1518548419970-58e3b4079ab2'), 'alt' => 'Sea temple on a rock off the Bali coast']],
                'places' => [
                    $place('Jakarta', 'java', 'Capital city', 'Istiqlal Mosque, the old town and city shopping.'),
                    $place('Bandung', 'java', 'West Java', 'Cool highlands, tea plantations and Tangkuban Perahu.'),
                    $place('Yogyakarta', 'java', '', 'Borobudur, Prambanan and the Sultan\'s Palace.'),
                    $place('Mount Bromo', 'java', 'East Java', 'Sunrise over a smoking volcano crater.'),
                    $place('Bali', 'bali-nusa-tenggara', 'Denpasar', 'Rice terraces, sea temples and sunset beaches.'),
                    $place('Lombok', 'bali-nusa-tenggara', 'Mataram', 'Quiet beaches, the Gili Islands and a thousand mosques.'),
                    $place('Medan & Lake Toba', 'sumatra', 'North Sumatra', 'A huge volcanic lake and Batak villages.'),
                    $place('Banda Aceh', 'sumatra', 'Aceh', 'The Baiturrahman Grand Mosque and the tsunami museum.'),
                    $place('Padang & Bukittinggi', 'sumatra', 'West Sumatra', 'Minangkabau culture, canyons and Padang cuisine.'),
                    $place('Makassar', 'sulawesi', 'South Sulawesi', 'Fort Rotterdam, seafood and the gateway to Toraja.'),
                ],
            ],
        ];
    }

    /**
     * Regions are stored as an ordered list because a JSON object column may reorder its keys.
     *
     * @param  array<string, string>  $regions
     * @return array<int, array{key: string, label: string}>
     */
    private function regionList(array $regions): array
    {
        return array_map(static fn (string $key, string $label): array => ['key' => $key, 'label' => $label], array_keys($regions), $regions);
    }

    private function mapTours(Closure $map): void
    {
        foreach (DB::table('group_tours_pages')->get(['id', 'tours']) as $page) {
            DB::table('group_tours_pages')->where('id', $page->id)->update([
                'tours' => json_encode(array_map($map, json_decode($page->tours, true) ?? [])),
            ]);
        }
    }
};
