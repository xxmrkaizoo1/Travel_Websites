<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_campaigns', function (Blueprint $table): void {
            $table->string('layout', 20)->default('collage')->after('design_key');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_campaigns', function (Blueprint $table): void {
            $table->dropColumn('layout');
        });
    }
};
