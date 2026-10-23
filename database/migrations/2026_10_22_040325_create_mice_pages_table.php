<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mice_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('hero_eyebrow');
            $table->string('hero_title');
            $table->string('hero_highlight');
            $table->text('hero_body');
            $table->string('hero_cta_label');
            $table->string('hero_image');
            $table->string('intro_eyebrow');
            $table->string('intro_heading');
            $table->string('intro_highlight');
            $table->text('intro_body');
            $table->string('intro_image');
            $table->string('intro_image_alt');
            $table->string('intro_image_caption');
            $table->string('why_eyebrow');
            $table->string('why_heading');
            $table->json('highlights');
            $table->string('contact_eyebrow');
            $table->string('contact_heading');
            $table->text('contact_body');
            $table->string('phone_primary');
            $table->string('phone_secondary');
            $table->string('email');
            $table->string('whatsapp_number');
            $table->string('clientele_eyebrow');
            $table->string('clientele_heading');
            $table->text('clientele_body');
            $table->timestamps();
        });

        DB::table('mice_pages')->insert([
            'hero_eyebrow' => 'SMART MUSLIM · MICE',
            'hero_title' => 'Company Trip',
            'hero_highlight' => '(MICE)',
            'hero_body' => 'Customised company trips for government and private sectors, handled and coordinated by Smart Muslim for the past 30 years.',
            'hero_cta_label' => 'Get a free quotation',
            'hero_image' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=2000&q=90',
            'intro_eyebrow' => 'COMPANY TRIP (MICE)',
            'intro_heading' => 'Trips planned around your',
            'intro_highlight' => 'team.',
            'intro_body' => "Smart Muslim specialises in handling and coordinating customised company trips involving both government and private sectors for the past 30 years.\n\nOur expertise is arranging tours that can be enjoyed by everyone from different religions and races in the same tour group, not only for Muslim tours.\n\nWe can customise anything as you wish, according to your budget, requests and needs.",
            'intro_image' => 'https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=1000&q=85',
            'intro_image_alt' => 'Company group on a corporate trip',
            'intro_image_caption' => 'One group, every background welcome.',
            'why_eyebrow' => 'WHY SMART MUSLIM',
            'why_heading' => 'Experience you can rely on.',
            'highlights' => json_encode([
                ['title' => '30 years of experience', 'body' => 'Trusted by both government and private sector organisations.'],
                ['title' => 'Inclusive for everyone', 'body' => 'Tours enjoyed by people of different religions and races in the same group.'],
                ['title' => 'Fully customisable', 'body' => 'Tailored to your budget, requests and needs.'],
                ['title' => 'Free quotation', 'body' => 'Contact us for further discussion, with no charges for quotations.'],
            ]),
            'contact_eyebrow' => 'SMART MUSLIM MICE DEPARTMENT',
            'contact_heading' => 'Let’s plan your company trip.',
            'contact_body' => 'Kindly contact us for further discussions. Quotation is free without charges!',
            'phone_primary' => '+6017-388 6708',
            'phone_secondary' => '+6016-550 0287',
            'email' => 'smart_muslim@roystartravel.com.my',
            'whatsapp_number' => '60173886708',
            'clientele_eyebrow' => 'TRUSTED BY',
            'clientele_heading' => 'Clientele (2023-2026)',
            'clientele_body' => 'Government agencies and leading private companies who have travelled with Smart Muslim.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mice_pages');
    }
};
