<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The tours page layout now serves two pages: Group Tours (the existing row) and Ground Tours,
 * a page of land-only packages reached from the Ground Tours menu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_tours_pages', function (Blueprint $table) {
            $table->string('kind', 20)->default('group')->after('id');
        });

        DB::table('group_tours_pages')->update(['kind' => 'group']);

        Schema::table('group_tours_pages', function (Blueprint $table) {
            $table->unique('kind');
        });

        $photo = static fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=900&q=85";
        $card = static fn (string $name, string $tagline, string $category, string $image, string $alt): array => ['name' => $name, 'tagline' => $tagline, 'category' => $category, 'image' => $image, 'alt' => $alt, 'link_url' => ''];

        DB::table('group_tours_pages')->insert([
            'kind' => 'ground',
            'meta_title' => 'Ground Tours | Smart Holidays by Roystar Travel & Tours',
            'meta_description' => 'Land-only ground tour packages with hotels, transport and guides arranged by Smart Holidays by Roystar Travel & Tours.',
            'hero_eyebrow' => 'GROUND TOURS',
            'heading' => 'Ground Destinations',
            'intro' => 'Land-only packages with hotels, transport and local guides arranged for you. Book your own flights and we take care of everything on the ground.',
            'hero_image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=2000&q=85',
            'tours' => json_encode([
                $card('Malaysia', 'Hotels, transfers and day tours around the peninsula and Borneo.', 'Domestic', $photo('1596422846543-75c6fc197f07'), 'Petronas Twin Towers and the Kuala Lumpur skyline at dusk'),
                $card('Thailand', 'Bangkok, Hat Yai and island stays with private transport.', 'International', $photo('1508009603885-50cf7c579365'), 'Neon signs in Bangkok at night'),
                $card('Indonesia', 'Bandung, Bali and Java with driver-guides and hotels.', 'International', $photo('1537996194471-e657df975ab4'), 'Lakeside temple in Bali, Indonesia'),
                $card('Vietnam', 'Hanoi, Ha Long Bay and Da Nang with local guides.', 'International', $photo('1528127269322-539801943592'), 'Boats among limestone islands in Ha Long Bay, Vietnam'),
            ]),
            'cta_heading' => 'Need a land package somewhere else?',
            'cta_body' => 'Tell us your destination, dates and group size, and our team will arrange the hotels, transport and guides.',
            'cta_label' => 'Ask us on WhatsApp',
            'booking_heading' => 'Booking Conditions',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('group_tours_pages')->where('kind', 'ground')->delete();

        Schema::table('group_tours_pages', function (Blueprint $table) {
            $table->dropUnique(['kind']);
            $table->dropColumn('kind');
        });
    }
};
