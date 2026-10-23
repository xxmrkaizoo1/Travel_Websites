<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mice_gallery_images', function (Blueprint $table): void {
            $table->id();
            $table->string('image');
            $table->string('caption')->default('');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        $seed = [
            ['https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1000&q=85', 'Corporate events and conferences'],
            ['https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=1000&q=85', 'Team building together'],
            ['images/series/hero.jpg', 'Memorable group journeys'],
            ['images/series/intro.jpg', 'Muslim-friendly itineraries'],
            ['images/series/seoul-1.jpg', 'Incentive trips abroad'],
            ['images/series/kunming-1.jpg', 'Discover new destinations'],
        ];

        foreach ($seed as $index => [$image, $caption]) {
            DB::table('mice_gallery_images')->insert([
                'image' => $image,
                'caption' => $caption,
                'position' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mice_gallery_images');
    }
};
