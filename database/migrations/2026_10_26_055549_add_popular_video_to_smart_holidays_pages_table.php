<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smart_holidays_pages', function (Blueprint $table): void {
            $table->string('popular_video', 2048)->nullable()->after('popular_body');
        });
    }

    public function down(): void
    {
        Schema::table('smart_holidays_pages', function (Blueprint $table): void {
            $table->dropColumn('popular_video');
        });
    }
};
