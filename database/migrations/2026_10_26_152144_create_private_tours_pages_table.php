<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Content for the Tour Package (Private) introduction page, editable from the admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('private_tours_pages', function (Blueprint $table) {
            $table->id();
            $table->string('meta_title');
            $table->text('meta_description');
            $table->string('hero_eyebrow');
            $table->string('hero_title');
            $table->string('hero_highlight');
            $table->text('hero_body');
            $table->string('hero_cta_label');
            $table->string('hero_image', 2048)->nullable();
            $table->string('hero_note')->nullable();
            $table->json('perks');
            $table->string('intro_label');
            $table->string('intro_heading');
            $table->string('intro_highlight')->nullable();
            $table->text('intro_body');
            $table->string('intro_link_label');
            $table->string('intro_image', 2048)->nullable();
            $table->string('intro_image_alt')->nullable();
            $table->string('intro_caption')->nullable();
            $table->string('destinations_label');
            $table->string('destinations_heading');
            $table->text('destinations_body')->nullable();
            $table->json('destinations');
            $table->string('contact_label');
            $table->string('contact_heading');
            $table->text('contact_body');
            $table->string('contact_cta_label');
            $table->timestamps();
        });

        $photo = static fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1000&q=85";
        $destination = static fn (string $name, string $tagline, string $linkUrl, string $linkLabel, string $image, string $alt): array => compact('name', 'tagline', 'image', 'alt') + ['link_url' => $linkUrl, 'link_label' => $linkLabel];

        DB::table('private_tours_pages')->insert([
            'meta_title' => 'Private Tour Packages | Smart Muslim by Roystar Travel & Tours',
            'meta_description' => 'Explore private tours by Smart Muslim, with thoughtfully planned journeys across Southeast Asia.',
            'hero_eyebrow' => 'SMART MUSLIM · PRIVATE TOURS',
            'hero_title' => 'Travel your way.',
            'hero_highlight' => 'Make it meaningful.',
            'hero_body' => 'Discover thoughtfully planned private journeys, with the flexibility to explore at your own pace and enjoy experiences that matter to you.',
            'hero_cta_label' => 'Explore destinations',
            'hero_image' => null,
            'hero_note' => 'PRIVATE JOURNEYS · THOUGHTFUL TRAVEL',
            'perks' => json_encode([
                ['icon' => '🍽️', 'title' => 'Halal meals', 'body' => 'Muslim-friendly restaurants chosen along the way.'],
                ['icon' => '🕌', 'title' => 'Prayer times planned in', 'body' => 'Time and places for prayer built into each day.'],
                ['icon' => '🚐', 'title' => 'Private transport', 'body' => 'Your own vehicle and guide, only for your group.'],
                ['icon' => '🗓️', 'title' => 'Your dates, your pace', 'body' => 'Travel when it suits you and change the plan as you like.'],
            ]),
            'intro_label' => 'TOUR PACKAGE (PRIVATE)',
            'intro_heading' => 'A journey planned around',
            'intro_highlight' => 'you.',
            'intro_body' => 'Choose the destinations that inspire you, travel with your own private itinerary and let our team help take care of the details. From island escapes to cultural discoveries, find a trip that fits your pace and interests.',
            'intro_link_label' => 'Choose your destination',
            'intro_image' => null,
            'intro_image_alt' => 'Tropical island scenery in Southeast Asia',
            'intro_caption' => 'Thoughtful journeys, made personal.',
            'destinations_label' => 'FIND YOUR NEXT JOURNEY',
            'destinations_heading' => 'Explore our destinations.',
            'destinations_body' => 'Start with a place that speaks to you. Each destination offers its own experiences, with private tour options to help you make the most of your stay.',
            'destinations' => json_encode([
                $destination('Island & Honeymoon', 'Find a slower pace with romantic stays, island scenery and time to unwind.', '/island-honeymoon', 'Explore island escapes', $photo('1514282401047-d79a71a590e8'), 'Overwater villas above a turquoise lagoon'),
                $destination('Indonesia', 'Discover Indonesia’s culture, coastlines, highlands and island landscapes.', '/indonesia', 'Explore Indonesia', $photo('1537996194471-e657df975ab4'), 'Lakeside temple in Bali, Indonesia'),
                $destination('Thailand', 'Explore lively cities, northern scenery, local markets and tropical shores.', '/thailand', 'Explore Thailand', $photo('1528181304800-259b08848526'), 'Golden temple roofs in Chiang Mai, Thailand'),
                $destination('Vietnam', 'Experience a rich blend of city life, heritage, scenery and local flavour.', '/vietnam', 'Explore Vietnam', $photo('1528127269322-539801943592'), 'Boats among limestone islands in Ha Long Bay, Vietnam'),
                $destination('Philippines', 'Make time for island scenery, welcoming communities and coastal discoveries.', '/philippines', 'Explore the Philippines', $photo('1518509562904-e7ef99cdcc86'), 'Limestone cliffs and a white sand cove in El Nido, Philippines'),
            ]),
            'contact_label' => 'LET’S PLAN YOUR TRIP',
            'contact_heading' => 'Your next journey starts here.',
            'contact_body' => 'Tell us where you would like to go and what you would love to experience. Our team will help you plan a private tour that feels right for you.',
            'contact_cta_label' => 'Talk to our team',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('private_tours_pages');
    }
};
