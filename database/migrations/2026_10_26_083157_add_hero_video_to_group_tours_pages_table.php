<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_tours_pages', function (Blueprint $table): void {
            $table->string('hero_video', 2048)->nullable()->after('hero_image');
        });
    }

    public function down(): void
    {
        Schema::table('group_tours_pages', function (Blueprint $table): void {
            $table->dropColumn('hero_video');
        });
    }
};
