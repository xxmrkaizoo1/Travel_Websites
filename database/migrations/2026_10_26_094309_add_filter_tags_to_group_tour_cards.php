<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tour cards get a filter tag for the Group Tours page filter. Malaysia is domestic for a
 * Malaysian agency; every other card starts as international. Existing tags are kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->mapTours(static fn (array $tour): array => [
            ...$tour,
            'category' => filled($tour['category'] ?? '') ? $tour['category'] : (($tour['name'] ?? '') === 'Malaysia' ? 'Domestic' : 'International'),
        ]);
    }

    public function down(): void
    {
        $this->mapTours(static fn (array $tour): array => array_diff_key($tour, ['category' => true]));
    }

    private function mapTours(Closure $map): void
    {
        foreach (DB::table('group_tours_pages')->get(['id', 'tours']) as $page) {
            DB::table('group_tours_pages')->where('id', $page->id)->update([
                'tours' => json_encode(array_map($map, json_decode($page->tours, true) ?? [])),
            ]);
        }
    }
};
