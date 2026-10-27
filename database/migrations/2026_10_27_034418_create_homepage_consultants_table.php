<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The travel consultants shown in the "Have a question?" section of the Smart Muslim homepage.
 * Starts with the four consultants from the previous website; their photos and WhatsApp numbers are added from the admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_consultants', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('position')->default(0);
            $table->string('name', 60);
            $table->string('role', 80)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('homepage_consultants')->insert(collect(['Badrul', 'Wani', 'Izzren', 'Azam'])
            ->map(fn (string $name, int $index): array => [
                'position' => $index + 1,
                'name' => $name,
                'role' => 'Travel Consultant',
                'whatsapp' => null,
                'photo_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_consultants');
    }
};
