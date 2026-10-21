<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('design_key');
            $table->unsignedTinyInteger('position')->unique();
            $table->string('title', 100);
            $table->text('overlay_label');
            $table->string('button_label', 100);
            $table->string('href', 2048);
            $table->string('image_path', 2048);
            $table->string('image_alt', 255);
            $table->timestamps();
        });

        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });

        $now = now();

        DB::table('homepage_services')->insert([
            [
                'slug' => 'rest-of-the-world',
                'design_key' => 'service-world',
                'position' => 1,
                'title' => 'Rest of the World',
                'overlay_label' => "REST OF\nTHE WORLD",
                'button_label' => 'Click Here!',
                'href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20your%20international%20tour%20packages.',
                'image_path' => 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=600&q=85',
                'image_alt' => 'A tranquil lake surrounded by mountains',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'china-muslim',
                'design_key' => 'service-china',
                'position' => 2,
                'title' => 'China Muslim',
                'overlay_label' => "CHINA\nMUSLIM",
                'button_label' => 'Click Here!',
                'href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20China%20Muslim%20tour%20packages.',
                'image_path' => 'https://images.unsplash.com/photo-1508804185872-d7badad00f7d?auto=format&fit=crop&w=600&q=85',
                'image_alt' => 'The Great Wall of China crossing a mountain ridge',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'company-trip',
                'design_key' => 'service-company',
                'position' => 3,
                'title' => 'Company Trip',
                'overlay_label' => "COMPANY\nTRIP",
                'button_label' => 'Click Here!',
                'href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20plan%20a%20company%20trip.',
                'image_path' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=85',
                'image_alt' => 'Colleagues working together around a table',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'ticketing',
                'design_key' => 'service-ticketing',
                'position' => 4,
                'title' => 'Ticketing',
                'overlay_label' => "FLIGHT\nTICKETS",
                'button_label' => 'Click Here!',
                'href' => 'mailto:tour@roystartravel.com.my?subject=Flight%20ticket%20enquiry',
                'image_path' => 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=600&q=85',
                'image_alt' => 'Passenger airplane flying above the clouds',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('homepage_settings')->insert([
            'key' => 'services_heading',
            'value' => 'OUR SERVICES',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_settings');
        Schema::dropIfExists('homepage_services');
    }
};
