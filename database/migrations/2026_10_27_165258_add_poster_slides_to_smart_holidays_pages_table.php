<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the automatic portrait poster slideshow shown below the featured destinations carousel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smart_holidays_pages', function (Blueprint $table): void {
            $table->string('posters_eyebrow')->default('FRESH DEALS');
            $table->string('posters_heading')->default('Trending Packages');
            $table->json('poster_slides')->nullable();
        });

        DB::table('smart_holidays_pages')->update([
            'poster_slides' => json_encode([
                ['image' => 'images/series/kunming-4.jpg', 'alt' => 'Traditional red pavilion in Kunming, China', 'label' => 'Muslim tours', 'title' => 'Kunming & Yunnan', 'price' => '', 'link_url' => '/china-muslim'],
                ['image' => 'images/ticketing/batik-air-explore-thailand.jpg', 'alt' => 'Batik Air Explore Thailand promotion poster', 'label' => 'Flight deals', 'title' => 'Explore Thailand', 'price' => '', 'link_url' => '/ticketing'],
                ['image' => 'images/series/danang-2.jpg', 'alt' => 'Golden Bridge held by giant stone hands in Da Nang', 'label' => 'Ground tours', 'title' => 'Da Nang, Vietnam', 'price' => '', 'link_url' => '/vietnam'],
                ['image' => 'images/ticketing/batik-air-explore-indonesia.jpg', 'alt' => 'Batik Air Explore Indonesia promotion poster', 'label' => 'Flight deals', 'title' => 'Explore Indonesia', 'price' => '', 'link_url' => '/ticketing'],
                ['image' => 'images/series/melbourne-5.jpg', 'alt' => 'Melbourne travel highlight', 'label' => 'Series departures', 'title' => 'Melbourne, Australia', 'price' => '', 'link_url' => '/tour/series-departures-2026'],
                ['image' => 'images/ticketing/batik-air-chase-the-horizon.jpg', 'alt' => 'Batik Air Chase the Horizon promotion poster', 'label' => 'Flight deals', 'title' => 'Chase the Horizon', 'price' => '', 'link_url' => '/ticketing'],
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('smart_holidays_pages', function (Blueprint $table): void {
            $table->dropColumn(['posters_eyebrow', 'posters_heading', 'poster_slides']);
        });
    }
};
