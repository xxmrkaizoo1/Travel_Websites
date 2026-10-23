<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('china_about_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('badge');
            $table->string('title');
            $table->text('intro');
            $table->json('facts');
            $table->text('body');
            $table->string('note');
            $table->json('gallery');
            $table->timestamps();
        });

        DB::table('china_about_pages')->insert([
            'badge' => 'PLACE GUIDE',
            'title' => 'About this place',
            'intro' => 'China is a vast country of ancient heritage and modern cities, from the Great Wall and Xi’an’s Terracotta Army to the skyline of Shanghai and the lakes of Hangzhou.',
            'facts' => json_encode([
                ['label' => 'Capital', 'value' => 'Beijing'],
                ['label' => 'Language', 'value' => 'Mandarin'],
                ['label' => 'Currency', 'value' => 'Renminbi (RMB / CNY)'],
                ['label' => 'Time', 'value' => 'Same as Malaysia (GMT+8)'],
            ]),
            'body' => 'Muslim communities live across China, and halal (Qingzhen) restaurants are easy to find in many cities. Our Smart Muslim tours arrange halal meals and prayer-friendly schedules throughout your trip.',
            'note' => 'Please check the latest passport, visa and travel requirements before you travel.',
            'gallery' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('china_about_pages');
    }
};
