<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Photos of our travellers on past China trips, shown as a slideshow on the China Muslim page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('china_about_pages', function (Blueprint $table) {
            $table->json('traveller_photos')->nullable();
        });

        DB::table('china_about_pages')->update([
            'traveller_photos' => json_encode([
                ['image' => 'images/china/travellers/lanzhou-silk-road.jpg', 'caption' => 'Lanzhou · Silk Road'],
                ['image' => 'images/china/travellers/zhengzhou-xian.jpg', 'caption' => 'Kembara Islamik · Zhengzhou & Xi’an'],
                ['image' => 'images/china/travellers/gansu-ningxia-qinghai.jpg', 'caption' => 'Gansu, Ningxia & Qinghai'],
                ['image' => 'images/china/travellers/shanghai-bund.jpg', 'caption' => 'Nanjing & Shanghai · The Bund by night'],
                ['image' => 'images/china/travellers/guangzhou-canton-tower.jpg', 'caption' => 'Guangzhou · Canton Tower'],
                ['image' => 'images/china/travellers/lanzhou-yellow-river.jpg', 'caption' => 'Lanzhou · Yellow River waterwheels'],
                ['image' => 'images/china/travellers/xinjiang-arrival.jpg', 'caption' => 'Xinjiang · Arrival day'],
                ['image' => 'images/china/travellers/mosque-visit.jpg', 'caption' => 'A mosque visit together'],
                ['image' => 'images/china/travellers/canyon-trail.jpg', 'caption' => 'Canyon trail'],
                ['image' => 'images/china/travellers/waterfall-stop.jpg', 'caption' => 'Exploring together'],
                ['image' => 'images/china/travellers/airport-departure.jpg', 'caption' => 'All smiles before take-off'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function down(): void
    {
        Schema::table('china_about_pages', function (Blueprint $table) {
            $table->dropColumn('traveller_photos');
        });
    }
};
