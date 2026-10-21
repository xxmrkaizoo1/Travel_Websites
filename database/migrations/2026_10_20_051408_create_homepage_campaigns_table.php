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
        Schema::create('homepage_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('design_key');
            $table->unsignedTinyInteger('position')->unique();
            $table->string('kicker', 100);
            $table->string('headline', 100);
            $table->string('headline_highlight', 100);
            $table->text('body');
            $table->string('cta_label', 100);
            $table->string('image_main', 2048);
            $table->string('image_top', 2048);
            $table->string('image_bottom', 2048);
            $table->string('image_alt', 255);
            $table->string('sticker', 100);
            $table->string('ribbon', 100);
            $table->string('brand_top', 50);
            $table->string('brand_bottom', 50);
            $table->timestamps();
        });

        $now = now();

        DB::table('homepage_campaigns')->insert([
            [
                'slug' => 'season-of-discovery',
                'design_key' => 'fair',
                'position' => 1,
                'kicker' => 'A new season of discovery',
                'headline' => 'See you',
                'headline_highlight' => 'out there.',
                'body' => "NEW TRAVEL EXPERIENCES\nPERSONAL TRIP PLANNING\nMEMORABLE MOMENTS",
                'cta_label' => "LET'S PLAN YOUR NEXT TRIP",
                'image_main' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=85',
                'image_top' => 'https://images.unsplash.com/photo-1530789253388-582c481c54b0?auto=format&fit=crop&w=400&q=80',
                'image_bottom' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=400&q=80',
                'image_alt' => 'A winding road through a wide mountain landscape',
                'sticker' => "TRAVEL\nTOGETHER",
                'ribbon' => 'SMART HOLIDAYS',
                'brand_top' => 'SMART',
                'brand_bottom' => 'MUSLIM',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'thoughtful-muslim-tours',
                'design_key' => 'muslim',
                'position' => 2,
                'kicker' => 'Travel with peace of mind',
                'headline' => 'More wonder.',
                'headline_highlight' => 'More meaning.',
                'body' => "THOUGHTFUL ITINERARIES\nHALAL-FRIENDLY EXPERIENCES\nSUPPORT ALONG THE WAY",
                'cta_label' => 'EXPLORE MUSLIM TOURS',
                'image_main' => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?auto=format&fit=crop&w=900&q=85',
                'image_top' => 'https://images.unsplash.com/photo-1519608487953-e999c86e7455?auto=format&fit=crop&w=400&q=80',
                'image_bottom' => 'https://images.unsplash.com/photo-1541432901042-2d8bd64b4a9b?auto=format&fit=crop&w=400&q=80',
                'image_alt' => 'The historic Blue Mosque in Istanbul',
                'sticker' => "MADE WITH\nCARE",
                'ribbon' => 'SMART MUSLIM TOURS',
                'brand_top' => 'SMART',
                'brand_bottom' => 'MUSLIM',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'group-departures',
                'design_key' => 'group',
                'position' => 3,
                'kicker' => 'The world is better shared',
                'headline' => 'Go further.',
                'headline_highlight' => 'Together.',
                'body' => "SMALL MOMENTS, BIG STORIES\nGREAT PLACES, NEW FRIENDS\nYOUR NEXT GROUP ADVENTURE",
                'cta_label' => 'FIND YOUR NEXT JOURNEY',
                'image_main' => 'https://images.unsplash.com/photo-1470252649378-9c29740c9fa8?auto=format&fit=crop&w=900&q=85',
                'image_top' => 'https://images.unsplash.com/photo-1467269204594-9661b134dd2b?auto=format&fit=crop&w=400&q=80',
                'image_bottom' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=400&q=80',
                'image_alt' => 'A mountain landscape opening into the morning light',
                'sticker' => "GOOD\nCOMPANY",
                'ribbon' => 'GROUP DEPARTURES',
                'brand_top' => 'SMART',
                'brand_bottom' => 'HOLIDAYS',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_campaigns');
    }
};
