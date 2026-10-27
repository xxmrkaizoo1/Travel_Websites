<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per visitor per liked Smart Muslim package. The package key is "tour-package/{slug}",
     * "tour-package/{slug}#{destination}" for a series destination, or "china-muslim-package/{id}".
     */
    public function up(): void
    {
        Schema::create('package_likes', function (Blueprint $table) {
            $table->id();
            $table->string('package_key', 191);
            $table->string('visitor_key', 80);
            $table->timestamps();

            $table->unique(['package_key', 'visitor_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_likes');
    }
};
