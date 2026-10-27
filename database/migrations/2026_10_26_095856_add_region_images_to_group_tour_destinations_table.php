<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Each region gets a photo for the region slides at the top of a destination page.
 * Malaysia starts with scenic photos the admin can replace.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_tour_destinations', function (Blueprint $table) {
            $table->json('region_images')->nullable()->after('states');
        });

        $photo = static fn (string $id, string $alt): array => [
            'image' => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1200&q=85",
            'alt' => $alt,
        ];

        DB::table('group_tour_destinations')->where('slug', 'malaysia')->update([
            'region_images' => json_encode([
                'northern' => $photo('1507525428034-b723cf961d3e', 'Soft sand and clear tropical sea'),
                'central' => $photo('1596422846543-75c6fc197f07', 'Petronas Twin Towers and the Kuala Lumpur skyline at dusk'),
                'southern' => $photo('1540541338287-41700207dee6', 'Seaside resort with an outdoor pool'),
                'east-coast' => $photo('1519046904884-53103b34b206', 'Palm trees on a white sand beach'),
                'borneo' => $photo('1500534623283-312aade485b7', 'Sunrise over misty mountain ridges'),
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('group_tour_destinations', function (Blueprint $table) {
            $table->dropColumn('region_images');
        });
    }
};
