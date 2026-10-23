<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mice_clients', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('logo');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        $clients = [
            'enGreen' => 'engreen.jpg',
            'Universiti Sains Islam Malaysia' => 'universiti-sains-islam-malaysia.png',
            'Salcon Engineering' => 'salcon.png',
            'UIAM' => 'uiam.png',
            'Harta Maintenance' => 'harta.png',
            'Duopharma' => 'duopharma.png',
            'Perodua' => 'perodua.png',
            'Meinhardt' => 'meinhardt.png',
            'MACM' => 'macm.png',
            'Henry Goh & Co' => 'henry-goh-co.jpg',
            'Universiti Teknologi MARA' => 'uitm.png',
            'Cosmo SCM' => 'cosmo-scm.png',
            'Iktisas Planners' => 'iktisas-planners.png',
        ];

        $position = 0;

        foreach ($clients as $name => $file) {
            DB::table('mice_clients')->insert([
                'name' => $name,
                'logo' => 'images/clientele/'.$file,
                'position' => ++$position,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mice_clients');
    }
};
