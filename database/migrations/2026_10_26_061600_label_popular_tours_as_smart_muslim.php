<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The popular tour cards come from the Smart Muslim series packages, so the section is labelled as
 * Smart Muslim. Only text still matching the original copy is changed; admin edits are kept.
 */
return new class extends Migration
{
    private const ORIGINAL = [
        'popular_eyebrow' => 'MADE FOR YOUR NEXT GETAWAY',
        'popular_heading' => 'Our Popular Tours',
        'popular_body' => 'Discover memorable places with journeys planned around you.',
    ];

    private const SMART_MUSLIM = [
        'popular_eyebrow' => 'SMART MUSLIM PACKAGES',
        'popular_heading' => 'Popular in Smart Muslim',
        'popular_body' => 'Muslim-friendly journeys with halal meals and prayer times planned in.',
    ];

    public function up(): void
    {
        $this->replace(self::ORIGINAL, self::SMART_MUSLIM);
    }

    public function down(): void
    {
        $this->replace(self::SMART_MUSLIM, self::ORIGINAL);
    }

    /**
     * @param  array<string, string>  $from
     * @param  array<string, string>  $to
     */
    private function replace(array $from, array $to): void
    {
        foreach ($from as $column => $value) {
            DB::table('smart_holidays_pages')->where($column, $value)->update([$column => $to[$column]]);
        }
    }
};
