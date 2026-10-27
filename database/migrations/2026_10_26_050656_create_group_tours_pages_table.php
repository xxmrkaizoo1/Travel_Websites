<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_tours_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('meta_title');
            $table->string('meta_description', 500);
            $table->string('heading');
            $table->string('intro', 500)->nullable();
            $table->json('tours');
            $table->timestamps();
        });

        DB::table('group_tours_pages')->insert([
            'meta_title' => 'Group Tours | Smart Holidays by Roystar Travel & Tours',
            'meta_description' => 'Explore our focus group tours to Malaysia, Vietnam, Thailand and Indonesia with Smart Holidays by Roystar Travel & Tours.',
            'heading' => 'Focus Tours',
            'intro' => null,
            'tours' => json_encode([
                ['name' => 'Malaysia', 'image' => 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=900&q=85', 'alt' => 'Petronas Twin Towers and the Kuala Lumpur skyline at dusk', 'link_url' => ''],
                ['name' => 'Vietnam', 'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=900&q=85', 'alt' => 'Boats among limestone islands in Ha Long Bay, Vietnam', 'link_url' => '/vietnam'],
                ['name' => 'Thailand', 'image' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?auto=format&fit=crop&w=900&q=85', 'alt' => 'Temple architecture in Thailand', 'link_url' => '/thailand'],
                ['name' => 'Indonesia', 'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=900&q=85', 'alt' => 'Coastal temple in Bali, Indonesia', 'link_url' => '/indonesia'],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('group_tours_pages');
    }
};
