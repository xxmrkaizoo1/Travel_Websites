<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Group tours are separate packages from Smart Muslim tours, so the default cards stop linking
 * to the Smart Muslim country pages. Links set by an admin to anything else are kept.
 */
return new class extends Migration
{
    private const SMART_MUSLIM_LINKS = [
        'Vietnam' => '/vietnam',
        'Thailand' => '/thailand',
        'Indonesia' => '/indonesia',
    ];

    public function up(): void
    {
        $this->replaceLinks(fn (array $tour): string => in_array($tour['link_url'] ?? '', self::SMART_MUSLIM_LINKS, true) ? '' : ($tour['link_url'] ?? ''));
    }

    public function down(): void
    {
        $this->replaceLinks(fn (array $tour): string => ($tour['link_url'] ?? '') === '' ? (self::SMART_MUSLIM_LINKS[$tour['name'] ?? ''] ?? '') : $tour['link_url']);
    }

    private function replaceLinks(Closure $link): void
    {
        foreach (DB::table('group_tours_pages')->get(['id', 'tours']) as $page) {
            $tours = array_map(
                fn (array $tour): array => ['link_url' => $link($tour), ...array_diff_key($tour, ['link_url' => true])],
                json_decode($page->tours, true) ?? [],
            );

            DB::table('group_tours_pages')->where('id', $page->id)->update(['tours' => json_encode($tours)]);
        }
    }
};
