<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('meta_title');
            $table->string('meta_description', 500);
            $table->string('hero_eyebrow');
            $table->string('hero_title');
            $table->string('hero_body', 500);
            $table->string('hero_image');
            $table->string('moments_eyebrow');
            $table->string('moments_heading');
            $table->string('moments_body', 500);
            $table->string('moments_card_label');
            $table->string('moments_card_eyebrow');
            $table->json('destinations');
            $table->timestamps();
        });

        DB::table('gallery_pages')->insert([
            'meta_title' => 'Our Gallery | Smart Muslim by Roystar Travel & Tours',
            'meta_description' => 'Explore the destinations of Smart Muslim by Roystar Travel & Tours.',
            'hero_eyebrow' => 'OUR JOURNEYS',
            'hero_title' => 'Smart Muslim Gallery',
            'hero_body' => 'A glimpse of the places and experiences waiting for you.',
            'hero_image' => 'images/series/danang-1.jpg',
            'moments_eyebrow' => 'POSTCARDS FROM EVERYWHERE',
            'moments_heading' => 'A closer look at the journey',
            'moments_body' => 'Take a little inspiration from the places, colors, and moments that make every trip unforgettable.',
            'moments_card_label' => 'A JOURNEY TO REMEMBER',
            'moments_card_eyebrow' => 'EXPLORE',
            'destinations' => json_encode([
                ['country' => 'South Korea', 'flag' => 'images/series/flag-kr.png', 'photo' => 'images/series/seoul-1.jpg', 'photo_alt' => 'Historic palace in South Korea'],
                ['country' => 'China', 'flag' => 'images/series/flag-cn.png', 'photo' => 'images/series/kunming-1.jpg', 'photo_alt' => 'Traditional Chinese architecture'],
                ['country' => 'Vietnam', 'flag' => 'images/series/flag-vn.png', 'photo' => 'images/series/danang-1.jpg', 'photo_alt' => 'Illuminated bridge over a river in Vietnam'],
                ['country' => 'Indonesia', 'flag' => 'images/series/flag-id.png', 'photo' => 'images/series/medan-1.jpg', 'photo_alt' => 'Waterfall surrounded by forest in Indonesia'],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_pages');
    }
};
