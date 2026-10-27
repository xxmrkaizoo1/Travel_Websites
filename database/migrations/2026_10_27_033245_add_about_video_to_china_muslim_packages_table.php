<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each card can show its own video in the "About this place" popup: an uploaded file or a YouTube link.
     */
    public function up(): void
    {
        Schema::table('china_muslim_packages', function (Blueprint $table) {
            $table->string('about_video_path')->nullable()->after('details');
            $table->string('about_video_url', 2048)->nullable()->after('about_video_path');
        });
    }

    public function down(): void
    {
        Schema::table('china_muslim_packages', function (Blueprint $table) {
            $table->dropColumn(['about_video_path', 'about_video_url']);
        });
    }
};
