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
        Schema::create('smart_muslim_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('smart_muslim_menus')
                ->cascadeOnDelete();
            $table->string('label', 80);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_muslim_menus');
    }
};
