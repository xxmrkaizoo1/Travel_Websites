<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smart_holidays_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('meta_title');
            $table->string('meta_description', 500);
            $table->string('hero_eyebrow');
            $table->string('hero_title');
            $table->text('hero_body');
            $table->string('hero_cta_label');
            $table->string('hero_secondary_label');
            $table->string('hero_image');
            $table->string('hero_image_alt');
            $table->json('stats');
            $table->string('services_eyebrow');
            $table->string('services_heading');
            $table->json('services');
            $table->json('journey_slides');
            $table->string('popular_eyebrow');
            $table->string('popular_heading');
            $table->string('popular_body', 500);
            $table->string('brands_eyebrow');
            $table->string('brands_heading');
            $table->string('cta_heading');
            $table->string('cta_body', 500);
            $table->string('cta_label');
            $table->string('whatsapp_number');
            $table->timestamps();
        });

        DB::table('smart_holidays_pages')->insert([
            'meta_title' => 'Smart Holidays by Roystar Travel & Tours',
            'meta_description' => 'Smart Holidays by Roystar Travel & Tours. Explore group tours, ground tours, domestic trips and Smart Muslim tours.',
            'hero_eyebrow' => 'WELCOME TO SMART HOLIDAYS',
            'hero_title' => 'Your world of journeys, thoughtfully planned.',
            'hero_body' => 'From group tours and ground arrangements to domestic getaways and Muslim-friendly holidays, Smart Holidays by Roystar Travel & Tours helps you explore the world your way.',
            'hero_cta_label' => 'Plan my trip',
            'hero_secondary_label' => 'Explore tours',
            'hero_image' => 'images/smart-holidays-banner.jpg',
            'hero_image_alt' => 'Smart Holidays travel banner featuring Big Ben, Swiss mountain train, Cappadocia balloons, Taiwan railway, Mount Fuji and Shanghai skyline',
            'stats' => json_encode([
                ['title' => 'Group', 'body' => 'Escorted tours'],
                ['title' => 'Ground', 'body' => 'Tailored arrangements'],
                ['title' => 'Domestic', 'body' => 'Local getaways'],
                ['title' => 'Muslim', 'body' => 'Friendly itineraries'],
            ]),
            'services_eyebrow' => 'WHAT WE DO',
            'services_heading' => 'Everything for your next escape.',
            'services' => json_encode([
                ['title' => 'Group Tours', 'body' => 'Join curated escorted journeys across Asia, Europe and beyond.'],
                ['title' => 'Ground Tours', 'body' => 'Transfers, guides and stays arranged seamlessly at your destination.'],
                ['title' => 'Domestic Tours', 'body' => 'Discover Malaysia with easy, well-paced local getaways.'],
                ['title' => 'Ticketing', 'body' => 'Flights and travel tickets handled by our friendly team.'],
            ]),
            'journey_slides' => json_encode([
                ['image' => 'images/series/seoul-1.jpg', 'alt' => 'Gyeongbokgung Palace in Seoul', 'eyebrow' => 'GROUP JOURNEYS', 'title' => 'Seoul, South Korea', 'body' => 'Palace mornings. Market evenings. A city full of contrasts.', 'link_label' => 'Explore group journeys', 'link_url' => '/smart-muslim', 'thumb' => 'Seoul'],
                ['image' => 'images/series/intro.jpg', 'alt' => 'Historic mosque domes and minarets in Istanbul', 'eyebrow' => 'MUSLIM-FRIENDLY TOURS', 'title' => 'Istanbul, Türkiye', 'body' => 'Remarkable history, welcoming stays, and thoughtful itineraries.', 'link_label' => 'Explore Muslim tours', 'link_url' => '/smart-muslim', 'thumb' => 'Istanbul'],
                ['image' => 'images/series/danang-1.jpg', 'alt' => 'Dragon Bridge illuminated at night in Da Nang', 'eyebrow' => 'GROUND TOURS', 'title' => 'Da Nang, Vietnam', 'body' => 'Coastal days, lantern-lit nights, and room to explore.', 'link_label' => 'Explore Vietnam', 'link_url' => '/vietnam', 'thumb' => 'Da Nang'],
                ['image' => 'images/series/kunming-1.jpg', 'alt' => 'Traditional architecture in Kunming, China', 'eyebrow' => 'YUNNAN ESCAPES', 'title' => 'Kunming, China', 'body' => 'Mountain air, old streets, and a slower way to see China.', 'link_label' => 'Explore China tours', 'link_url' => '/china-muslim', 'thumb' => 'Kunming'],
                ['image' => 'images/series/danang-4.jpg', 'alt' => 'Da Nang city framed by green limestone mountains', 'eyebrow' => 'COASTAL GETAWAYS', 'title' => 'Marble Mountains, Vietnam', 'body' => 'Limestone peaks and coastal views just beyond the city.', 'link_label' => 'Plan a Vietnam trip', 'link_url' => '/vietnam', 'thumb' => 'Marble Mountains'],
            ]),
            'popular_eyebrow' => 'MADE FOR YOUR NEXT GETAWAY',
            'popular_heading' => 'Our Popular Tours',
            'popular_body' => 'Discover memorable places with journeys planned around you.',
            'brands_eyebrow' => 'OUR BRANDS',
            'brands_heading' => 'Choose how you want to travel.',
            'cta_heading' => 'Ready for your next journey?',
            'cta_body' => 'Talk to our travel team and get a plan made for you.',
            'cta_label' => 'WhatsApp Us',
            'whatsapp_number' => '60165500590',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_holidays_pages');
    }
};
