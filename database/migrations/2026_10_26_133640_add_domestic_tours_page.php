<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the Domestic Tours page: trips within Malaysia, reached from the Domestic Tour menu.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('group_tours_pages')->where('kind', 'domestic')->exists()) {
            return;
        }

        $photo = static fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=900&q=85";
        $card = static fn (string $name, string $tagline, string $category, string $image, string $alt): array => ['name' => $name, 'tagline' => $tagline, 'category' => $category, 'image' => $image, 'alt' => $alt, 'link_url' => ''];

        DB::table('group_tours_pages')->insert([
            'kind' => 'domestic',
            'meta_title' => 'Domestic Tours in Malaysia | Smart Holidays by Roystar Travel & Tours',
            'meta_description' => 'Domestic tour packages around Malaysia, from city breaks to islands and highlands, by Smart Holidays by Roystar Travel & Tours.',
            'hero_eyebrow' => 'DOMESTIC TOURS · MALAYSIA',
            'heading' => 'Explore Malaysia',
            'intro' => 'City breaks, island getaways and highland escapes close to home. Choose a destination and we will arrange the trip for you.',
            'hero_image' => 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=2000&q=85',
            'tours' => json_encode([
                $card('Kuala Lumpur', 'Twin Towers, Batu Caves and city shopping.', 'City', $photo('1596422846543-75c6fc197f07'), 'Petronas Twin Towers and the Kuala Lumpur skyline at dusk'),
                $card('Langkawi', 'Island hopping, cable car views and duty-free shopping.', 'Island', $photo('1507525428034-b723cf961d3e'), 'Soft sand and clear tropical sea'),
                $card('Terengganu', 'Redang and Perhentian islands with clear water for snorkelling.', 'Island', $photo('1519046904884-53103b34b206'), 'Palm trees on a white sand beach'),
                $card('Sabah', 'Mount Kinabalu, island parks and Borneo wildlife.', 'Nature', $photo('1500534623283-312aade485b7'), 'Sunrise over misty mountain ridges'),
                $card('Johor', 'Desaru beach resorts and family theme parks.', 'Resort', $photo('1540541338287-41700207dee6'), 'Seaside resort with an outdoor pool'),
            ]),
            'cta_heading' => 'Planning a trip within Malaysia?',
            'cta_body' => 'Tell us where you would like to go, your dates and group size, and our team will plan it for you.',
            'cta_label' => 'Ask us on WhatsApp',
            'booking_heading' => 'Booking Conditions',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('group_tours_pages')->where('kind', 'domestic')->delete();
    }
};
