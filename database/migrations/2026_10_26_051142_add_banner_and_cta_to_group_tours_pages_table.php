<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TAGLINES = [
        'Malaysia' => 'City lights, rainforests and island escapes.',
        'Vietnam' => 'Limestone bays, lantern towns and street food.',
        'Thailand' => 'Golden temples, night markets and beaches.',
        'Indonesia' => 'Volcano views, rice terraces and Bali sunsets.',
    ];

    public function up(): void
    {
        Schema::table('group_tours_pages', function (Blueprint $table): void {
            $table->string('hero_eyebrow')->default('GROUP TOURS')->after('meta_description');
            $table->string('hero_image')->default('images/smart-holidays-banner.jpg')->after('intro');
            $table->string('cta_heading')->default("Can't find your destination?")->after('tours');
            $table->string('cta_body', 500)->default('Tell us where you would like to go and our team will plan a group departure for you.')->after('cta_heading');
            $table->string('cta_label')->default('Ask us on WhatsApp')->after('cta_body');
        });

        foreach (DB::table('group_tours_pages')->get(['id', 'intro', 'tours']) as $page) {
            $tours = array_map(
                fn (array $tour): array => ['tagline' => self::TAGLINES[$tour['name']] ?? '', ...$tour],
                json_decode($page->tours, true) ?? [],
            );

            DB::table('group_tours_pages')->where('id', $page->id)->update([
                'intro' => $page->intro ?? 'Travel together on carefully planned group journeys across Asia, with everything arranged so you can simply enjoy the trip.',
                'tours' => json_encode($tours),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('group_tours_pages', function (Blueprint $table): void {
            $table->dropColumn(['hero_eyebrow', 'hero_image', 'cta_heading', 'cta_body', 'cta_label']);
        });
    }
};
