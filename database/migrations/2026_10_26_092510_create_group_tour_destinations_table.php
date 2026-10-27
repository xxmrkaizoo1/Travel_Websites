<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Malaysia's 13 states and 3 federal territories. Photos are left for the admin to upload.
     *
     * @var array<int, array{0: string, 1: string, 2: string, 3: string}>
     */
    private const MALAYSIA_STATES = [
        ['Perlis', 'northern', 'Kangar', 'The smallest state, with paddy fields and limestone hills on the Thai border.'],
        ['Kedah', 'northern', 'Alor Setar', 'Rice bowl of Malaysia and home to the Langkawi islands.'],
        ['Penang', 'northern', 'George Town', 'UNESCO heritage streets, street art and famous hawker food.'],
        ['Perak', 'northern', 'Ipoh', 'Limestone cave temples, old town cafés and Pangkor Island.'],
        ['Selangor', 'central', 'Shah Alam', 'Batu Caves, royal Klang and fireflies at Kuala Selangor.'],
        ['Kuala Lumpur', 'central', 'Federal Territory', 'The capital city of the Petronas Twin Towers and Bukit Bintang.'],
        ['Putrajaya', 'central', 'Federal Territory', 'The administrative capital, with the pink Putra Mosque by the lake.'],
        ['Negeri Sembilan', 'central', 'Seremban', 'Minangkabau architecture and the beaches of Port Dickson.'],
        ['Melaka', 'southern', 'Melaka City', 'UNESCO heritage town with Dutch Square and Jonker Street.'],
        ['Johor', 'southern', 'Johor Bahru', 'Desaru beaches, family theme parks and the gateway to Singapore.'],
        ['Pahang', 'east-coast', 'Kuantan', 'Cool Cameron Highlands, Genting and the Taman Negara rainforest.'],
        ['Terengganu', 'east-coast', 'Kuala Terengganu', 'Clear waters of Redang and Perhentian, and the Crystal Mosque.'],
        ['Kelantan', 'east-coast', 'Kota Bharu', 'Malay culture, crafts and lively traditional markets.'],
        ['Sabah', 'borneo', 'Kota Kinabalu', 'Mount Kinabalu, island hopping and Borneo wildlife.'],
        ['Sarawak', 'borneo', 'Kuching', 'Rainforests, longhouses and the riverside city of Kuching.'],
        ['Labuan', 'borneo', 'Federal Territory', 'A duty-free island known for its calm beaches and wreck diving.'],
    ];

    public function up(): void
    {
        Schema::create('group_tour_destinations', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('meta_description', 500);
            $table->string('hero_eyebrow');
            $table->string('heading');
            $table->string('intro', 500)->nullable();
            $table->string('hero_image', 2048);
            $table->string('states_heading');
            $table->json('states');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        DB::table('group_tour_destinations')->insert([
            'slug' => 'malaysia',
            'name' => 'Malaysia',
            'meta_description' => 'Explore group tours across the states of Malaysia with Smart Holidays by Roystar Travel & Tours.',
            'hero_eyebrow' => 'GROUP TOURS · MALAYSIA',
            'heading' => 'Discover Malaysia',
            'intro' => 'From island beaches to cool highlands and Borneo rainforests, choose the states you would like to explore together.',
            'hero_image' => 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=2000&q=85',
            'states_heading' => 'Explore the states',
            'states' => json_encode(array_map(
                static fn (array $state): array => ['name' => $state[0], 'region' => $state[1], 'capital' => $state[2], 'tagline' => $state[3], 'image' => '', 'alt' => ''],
                self::MALAYSIA_STATES,
            )),
            'position' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $page = DB::table('group_tours_pages')->first(['id', 'tours']);

        if ($page !== null) {
            $tours = array_map(
                static fn (array $tour): array => ($tour['name'] ?? '') === 'Malaysia' && blank($tour['link_url'] ?? '') ? [...$tour, 'link_url' => '/group-tours/malaysia'] : $tour,
                json_decode($page->tours, true) ?? [],
            );

            DB::table('group_tours_pages')->where('id', $page->id)->update(['tours' => json_encode($tours)]);
        }
    }

    public function down(): void
    {
        $page = DB::table('group_tours_pages')->first(['id', 'tours']);

        if ($page !== null) {
            $tours = array_map(
                static fn (array $tour): array => ($tour['link_url'] ?? '') === '/group-tours/malaysia' ? [...$tour, 'link_url' => ''] : $tour,
                json_decode($page->tours, true) ?? [],
            );

            DB::table('group_tours_pages')->where('id', $page->id)->update(['tours' => json_encode($tours)]);
        }

        Schema::dropIfExists('group_tour_destinations');
    }
};
