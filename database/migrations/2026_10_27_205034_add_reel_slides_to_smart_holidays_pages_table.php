<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the short-video reel strip shown below the brand cards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smart_holidays_pages', function (Blueprint $table): void {
            $table->string('reels_eyebrow')->default('WATCH & WANDER');
            $table->string('reels_heading')->default('Moments from the road');
            $table->json('reel_slides')->nullable();
        });

        $clip = static fn (int $id, string $suffix): string => "https://assets.mixkit.co/videos/{$id}/{$id}-{$suffix}";

        DB::table('smart_holidays_pages')->update([
            'reel_slides' => json_encode([
                ['video' => $clip(11303, '720.mp4'), 'image' => $clip(11303, 'thumb-720-0.jpg'), 'label' => 'Take off', 'title' => 'Above the clouds', 'link_url' => '/ticketing'],
                ['video' => $clip(11922, '720.mp4'), 'image' => $clip(11922, 'thumb-720-0.jpg'), 'label' => 'Muslim-friendly', 'title' => 'Mosques of Istanbul', 'link_url' => '/smart-muslim'],
                ['video' => $clip(51500, '720.mp4'), 'image' => $clip(51500, 'thumb-720-0.jpg'), 'label' => 'Island escapes', 'title' => 'Turquoise shores', 'link_url' => '/island-honeymoon'],
                ['video' => $clip(4231, '720.mp4'), 'image' => $clip(4231, 'thumb-720-0.jpg'), 'label' => 'City breaks', 'title' => 'Tokyo after rain', 'link_url' => '/group-tours'],
                ['video' => $clip(41576, '720.mp4'), 'image' => $clip(41576, 'thumb-720-0.jpg'), 'label' => 'Ground tours', 'title' => 'Winding mountain roads', 'link_url' => '/ground-tours'],
                ['video' => $clip(15694, '720.mp4'), 'image' => $clip(15694, 'thumb-720-0.jpg'), 'label' => 'Heritage', 'title' => 'Ottoman domes', 'link_url' => '/smart-muslim'],
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('smart_holidays_pages', function (Blueprint $table): void {
            $table->dropColumn(['reels_eyebrow', 'reels_heading', 'reel_slides']);
        });
    }
};
