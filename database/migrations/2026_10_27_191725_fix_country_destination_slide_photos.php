<?php

use App\Models\TourPackagePage;
use Illuminate\Database\Migrations\Migration;

/**
 * Replaces placeholder photos on the country pages' destination slides (a desert road for Manila,
 * a neon street for Medan…) with photos of the actual places. A slide is only changed while it
 * still shows its original placeholder, so photos an admin picked are kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->replacements() as $slug => $slides) {
            $page = TourPackagePage::query()->where('slug', $slug)->first();

            if ($page === null || empty($page->slides)) {
                continue;
            }

            $page->slides = array_map(function (array $slide) use ($slides): array {
                [$placeholder, $image, $alt] = $slides[$slide['title'] ?? ''] ?? [null, null, null];

                if ($placeholder !== null && str_contains((string) ($slide['image'] ?? ''), $placeholder)) {
                    $slide['image'] = $image;
                    $slide['alt'] = $alt;
                }

                return $slide;
            }, $page->slides);
            $page->saveQuietly();
        }
    }

    public function down(): void
    {
        // The placeholder photos are not worth restoring.
    }

    /**
     * Per page, slide title => [placeholder photo id, new image, new alt text].
     *
     * @return array<string, array<string, array{0: string, 1: string, 2: string}>>
     */
    private function replacements(): array
    {
        $u = static fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1200&q=85";

        return [
            'indonesia' => [
                'Aceh' => ['1518005020951', $u('1507525428034-b723cf961d3e'), 'Tropical beach on the Aceh coast at sunset'],
                'Medan' => ['1519608487953', 'images/series/medan-1.jpg', 'Sipiso-piso Waterfall in North Sumatra'],
                'Padang' => ['1500530855697', $u('1754615842174-7e6c8d1f0ca3'), 'Jam Gadang clock tower in Bukittinggi'],
                'Surabaya' => ['1519608487953', $u('1666887799869-4fbeda25d17f'), 'Mount Bromo surrounded by morning clouds'],
                'Yogyakarta' => ['1518709268805', $u('1566559532224-6d65e9fc0f37'), 'Stone stupas of Borobudur Temple'],
            ],
            'thailand' => [
                'Bangkok' => ['1508009603885', $u('1747235156815-3b39a7097b7b'), 'Wat Arun temple rising over Bangkok'],
                'Pattaya' => ['1507525428034', $u('1620373901514-4319a7c81cdb'), 'Pattaya Beach lined with hotels'],
                'Krabi' => ['1500375592092', 'images/island-honeymoon/krabi-longtail-boats.jpg', 'Longtail boats on a Krabi beach'],
                'Kanchanaburi' => ['1500530855697', $u('1624807177672-468d85ff1803'), 'Railway beside the River Kwai in Kanchanaburi'],
            ],
            'vietnam' => [
                'Mekong Delta' => ['1500530855697', $u('1519458498024-9448c70cbae7'), 'Boats on a river in the Mekong Delta'],
            ],
            'philippines' => [
                'Boracay' => ['1518509562904', $u('1553195029-754fbd369560'), 'White Beach in Boracay with sailboats offshore'],
                'Palawan' => ['1518509562904', $u('1583685173048-342a162bb888'), 'Aerial view of the Big Lagoon in El Nido, Palawan'],
                'Manila' => ['1500530855697', $u('1736122094944-da4deea774ab'), 'Fort Santiago gate in Intramuros, Manila'],
                'Cebu' => ['1507525428034', $u('1586263426392-3b3e0748f618'), 'Kawasan Falls in Cebu'],
                'Bohol' => ['1500375592092', $u('1546068996-8da61faeceaa'), 'The Chocolate Hills of Bohol'],
            ],
        ];
    }
};
