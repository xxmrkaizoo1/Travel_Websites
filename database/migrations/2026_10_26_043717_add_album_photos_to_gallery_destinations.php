<?php

use App\Models\GalleryPage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $page = GalleryPage::query()->first();

        if ($page === null) {
            return;
        }

        $albums = $this->albums();

        $page->update(['destinations' => array_map(
            fn (array $destination): array => empty($destination['photos']) && isset($albums[$destination['country']])
                ? [...$destination, 'photos' => $albums[$destination['country']]]
                : $destination,
            $page->destinations,
        )]);
    }

    public function down(): void
    {
        $page = GalleryPage::query()->first();

        if ($page === null) {
            return;
        }

        $page->update(['destinations' => array_map(
            fn (array $destination): array => array_diff_key($destination, ['photos' => true]),
            $page->destinations,
        )]);
    }

    /**
     * Popup album photos per country, using the bundled series destination photos.
     *
     * @return array<string, array<int, array{image: string, caption: string}>>
     */
    private function albums(): array
    {
        return [
            'South Korea' => [
                ['image' => 'images/series/seoul-1.jpg', 'caption' => 'Gyeongbokgung Palace'],
                ['image' => 'images/series/seoul-2.jpg', 'caption' => 'N Seoul Tower'],
                ['image' => 'images/series/seoul-3.jpg', 'caption' => 'Myeongdong'],
                ['image' => 'images/series/seoul-4.jpg', 'caption' => 'Han River'],
                ['image' => 'images/series/seoul-5.jpg', 'caption' => 'Starfield Library'],
            ],
            'China' => [
                ['image' => 'images/series/kunming-1.jpg', 'caption' => 'Green Lake Park, Kunming'],
                ['image' => 'images/series/kunming-2.jpg', 'caption' => 'Stone Forest'],
                ['image' => 'images/series/kunming-3.jpg', 'caption' => 'Jade Dragon Snow Mountain'],
                ['image' => 'images/series/kunming-4.jpg', 'caption' => 'Lijiang Old Town'],
                ['image' => 'images/series/kunming-5.jpg', 'caption' => 'Dian Lake'],
            ],
            'Vietnam' => [
                ['image' => 'images/series/danang-1.jpg', 'caption' => 'Dragon Bridge, Da Nang'],
                ['image' => 'images/series/danang-2.jpg', 'caption' => 'My Khe Beach'],
                ['image' => 'images/series/danang-3.jpg', 'caption' => 'Golden Bridge, Ba Na Hills'],
                ['image' => 'images/series/danang-4.jpg', 'caption' => 'Marble Mountains'],
                ['image' => 'images/series/danang-5.jpg', 'caption' => 'Hoi An Ancient Town'],
            ],
            'Indonesia' => [
                ['image' => 'images/series/medan-1.jpg', 'caption' => 'Sipiso-piso Waterfall'],
                ['image' => 'images/series/medan-2.jpg', 'caption' => 'Lake Toba'],
                ['image' => 'images/series/medan-3.jpg', 'caption' => 'Maimun Palace'],
                ['image' => 'images/series/medan-4.jpg', 'caption' => 'Great Mosque of Medan'],
                ['image' => 'images/series/medan-5.jpg', 'caption' => 'Samosir Island'],
            ],
        ];
    }
};
