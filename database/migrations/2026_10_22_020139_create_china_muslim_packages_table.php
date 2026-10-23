<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('china_muslim_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('position')->default(0)->index();
            $table->string('title', 160);
            $table->string('image_path');
            $table->unsignedInteger('price')->default(0);
            $table->unsignedTinyInteger('days');
            $table->unsignedTinyInteger('nights');
            $table->boolean('is_hot')->default(false);
            $table->string('link', 2048)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('china_muslim_packages');
    }
};
