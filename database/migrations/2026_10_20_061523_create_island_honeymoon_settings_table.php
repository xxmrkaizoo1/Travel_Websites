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
        Schema::create('island_honeymoon_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_eyebrow', 120);
            $table->string('hero_headline', 120);
            $table->string('hero_highlight', 160);
            $table->text('hero_body');
            $table->string('hero_cta_label', 100);
            $table->string('hero_note', 120);
            $table->string('hero_image', 2048);
            $table->string('intro_eyebrow', 120);
            $table->string('intro_headline', 160);
            $table->string('intro_highlight', 100);
            $table->text('intro_body');
            $table->string('intro_link_label', 120);
            $table->string('intro_link_href', 2048);
            $table->string('intro_image', 2048);
            $table->string('intro_image_alt', 255);
            $table->string('intro_image_caption', 120);
            $table->string('highlights_eyebrow', 120);
            $table->string('highlights_headline', 160);
            $table->string('highlights_body', 255);
            $table->json('highlights');
            $table->string('contact_eyebrow', 120);
            $table->string('contact_headline', 160);
            $table->text('contact_body');
            $table->string('contact_cta_label', 100);
            $table->string('contact_href', 2048);
            $table->timestamps();
        });

        $now = now();

        DB::table('island_honeymoon_settings')->insert([
            'hero_eyebrow' => 'PRIVATE GETAWAYS, MADE FOR YOU',
            'hero_headline' => 'Island escapes',
            'hero_highlight' => '& honeymoon moments',
            'hero_body' => 'Slow down, reconnect and discover beautiful places together. We’ll help you shape a private island getaway around the moments that matter to you.',
            'hero_cta_label' => 'Plan your getaway',
            'hero_note' => 'YOUR NEXT CHAPTER STARTS HERE',
            'hero_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=2000&q=90',
            'intro_eyebrow' => 'A LITTLE MORE PERSONAL',
            'intro_headline' => 'A getaway that feels like',
            'intro_highlight' => 'yours.',
            'intro_body' => 'From your first idea to the details of your itinerary, we can help you explore private island and honeymoon options at a pace that suits you. Share your preferred destination, travel dates and the kind of experience you have in mind, and our team can help you plan the next step.',
            'intro_link_label' => 'Talk to our travel team',
            'intro_link_href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20plan%20an%20Island%20%26%20Honeymoon%20getaway.',
            'intro_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1000&q=85',
            'intro_image_alt' => 'Turquoise water and a quiet tropical beach',
            'intro_image_caption' => 'A place to pause, together.',
            'highlights_eyebrow' => 'YOUR TRIP, YOUR WAY',
            'highlights_headline' => 'Make room for what matters.',
            'highlights_body' => 'Thoughtful planning starts with your idea of the perfect escape.',
            'highlights' => json_encode([
                [
                    'title' => 'Choose your setting',
                    'body' => 'Dreaming of a quiet beach, an island adventure or a scenic hideaway? Tell us what draws you in.',
                ],
                [
                    'title' => 'Celebrate your way',
                    'body' => 'Mark a honeymoon, anniversary or simply some time away together with a trip shaped around you.',
                ],
                [
                    'title' => 'Plan the details',
                    'body' => 'Share your preferences and questions with our team as you consider the itinerary that feels right.',
                ],
            ], JSON_THROW_ON_ERROR),
            'contact_eyebrow' => 'LET’S MAKE IT PERSONAL',
            'contact_headline' => 'Tell us what your perfect escape looks like.',
            'contact_body' => 'Share a little about your destination, dates and travel style. Our team will help you explore the possibilities.',
            'contact_cta_label' => 'Enquire on WhatsApp',
            'contact_href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20plan%20an%20Island%20%26%20Honeymoon%20getaway.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('island_honeymoon_settings');
    }
};
