<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('island_destinations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('position')->unique();
            $table->string('name', 120);
            $table->string('country', 100);
            $table->text('description');
            $table->string('known_as', 120);
            $table->string('perfect_for', 160);
            $table->string('benefits_heading', 160);
            $table->json('benefits');
            $table->string('feature_image', 2048);
            $table->string('feature_image_alt', 255);
            $table->string('feature_caption', 120);
            $table->json('gallery');
            $table->timestamps();
        });

        $now = now();

        DB::table('island_destinations')->insert([
            'slug' => 'bali-indonesia',
            'position' => 1,
            'name' => 'Bali Island',
            'country' => 'Indonesia',
            'description' => 'Bali, known as the “Island of the Gods,” is one of the world’s most popular holiday destinations, celebrated for its natural beauty, rich culture and peaceful tropical atmosphere. Located in Indonesia, Bali offers experiences for honeymooners, nature lovers, families and solo travellers.',
            'known_as' => 'Island of the Gods',
            'perfect_for' => 'Couples, families & explorers',
            'benefits_heading' => 'Why travellers love Bali',
            'benefits' => json_encode([
                ['icon' => '🌺', 'title' => 'Stunning natural beauty', 'body' => 'From the beaches of Kuta, Seminyak and Nusa Dua to Ubud’s terraced rice fields, enjoy picturesque places to unwind and take in the views.'],
                ['icon' => '🏛️', 'title' => 'Rich culture and arts', 'body' => 'Discover ancient temples, traditional dances, Balinese art and cultural ceremonies woven into local life.'],
                ['icon' => '🏄', 'title' => 'Exciting activities', 'body' => 'Explore surfing, snorkelling, Mount Batur hikes and boat trips to nearby islands such as Nusa Penida and Nusa Lembongan.'],
                ['icon' => '🍽️', 'title' => 'Delicious, halal-friendly food', 'body' => 'Enjoy a wide choice of local and international cuisine, including halal restaurants for Muslim travellers.'],
                ['icon' => '🛏️', 'title' => 'Stays for every budget', 'body' => 'Choose from luxurious private-pool villas and beachfront resorts to comfortable, budget-friendly homestays.'],
            ], JSON_THROW_ON_ERROR),
            'feature_image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=85',
            'feature_image_alt' => 'Tropical resort pool surrounded by palms in Bali',
            'feature_caption' => 'Island time, your way',
            'gallery' => json_encode([
                ['image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1600&q=85', 'alt' => 'Traditional Balinese temple gate framed by tropical scenery', 'eyebrow' => 'Discover Bali', 'title' => 'Culture & heritage'],
                ['image' => 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1600&q=85', 'alt' => 'Dramatic tropical coastline and blue ocean in Indonesia', 'eyebrow' => 'Find your coastline', 'title' => 'Coastal escapes'],
                ['image' => 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1600&q=85', 'alt' => 'A lush green tropical landscape beneath a bright sky', 'eyebrow' => 'Take the scenic route', 'title' => 'Nature & discovery'],
                ['image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=85', 'alt' => 'Soft sand and clear tropical sea', 'eyebrow' => 'Make time for blue skies', 'title' => 'Days by the sea'],
                ['image' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=1600&q=85', 'alt' => 'Seaside resort with an inviting outdoor pool', 'eyebrow' => 'Slow down and settle in', 'title' => 'Time to unwind'],
            ], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('island_destinations');
    }
};
