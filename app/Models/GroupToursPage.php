<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupToursPage extends Model
{
    public const MAX_TOURS = 16;

    public const MAX_BOOKING_CONDITIONS = 20;

    /** Largest banner video an admin may upload (50 MB). */
    public const MAX_VIDEO_KILOBYTES = 51200;

    public const WHATSAPP_NUMBER = '60165500590';

    public const KIND_GROUP = 'group';

    public const KIND_GROUND = 'ground';

    public const KIND_DOMESTIC = 'domestic';

    /**
     * Each page kind's name and its public and admin route names.
     *
     * @var array<string, array{label: string, route: string, admin_route: string}>
     */
    public const KINDS = [
        self::KIND_GROUP => ['label' => 'Group Tours', 'route' => 'group-tours', 'admin_route' => 'admin.group-tours'],
        self::KIND_GROUND => ['label' => 'Ground Tours', 'route' => 'ground-tours', 'admin_route' => 'admin.ground-tours'],
        self::KIND_DOMESTIC => ['label' => 'Domestic Tours', 'route' => 'domestic-tours', 'admin_route' => 'admin.domestic-tours'],
    ];

    protected $fillable = [
        'kind',
        'meta_title',
        'meta_description',
        'hero_eyebrow',
        'heading',
        'intro',
        'hero_image',
        'hero_video',
        'tours',
        'cta_heading',
        'cta_body',
        'cta_label',
        'booking_heading',
        'booking_intro',
        'booking_conditions',
    ];

    protected function casts(): array
    {
        return [
            'tours' => 'array',
            'booking_conditions' => 'array',
        ];
    }

    public static function forKind(string $kind): self
    {
        return self::query()->where('kind', $kind)->firstOrFail();
    }

    /**
     * The page's name, e.g. "Group Tours".
     */
    public function label(): string
    {
        return self::KINDS[$this->kind ?? self::KIND_GROUP]['label'];
    }

    public function publicUrl(): string
    {
        return route(self::KINDS[$this->kind ?? self::KIND_GROUP]['route']);
    }

    /**
     * Admin route name for this page, e.g. adminRoute('update') is "admin.ground-tours.update".
     */
    public function adminRoute(string $action): string
    {
        return self::KINDS[$this->kind ?? self::KIND_GROUP]['admin_route'].'.'.$action;
    }

    public function imageUrl(string $path): string
    {
        if (in_array(parse_url($path, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    public function whatsappUrl(): string
    {
        return 'https://wa.me/'.self::WHATSAPP_NUMBER;
    }

    /**
     * Distinct filter tags used by the tour cards, in card order.
     *
     * @return array<int, string>
     */
    public function tourCategories(): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (array $tour): string => trim((string) ($tour['category'] ?? '')),
            $this->tours ?? [],
        ))));
    }

    /**
     * Page anchor of a tour card, e.g. "tour-malaysia".
     */
    public function tourAnchor(string $tourName): string
    {
        return 'tour-'.Str::slug($tourName);
    }

    /**
     * Where a menu link to a tour card goes: the card's own page, or the card on this page.
     */
    public function tourMenuUrl(array $tour): string
    {
        return filled($tour['link_url'] ?? '') ? $tour['link_url'] : $this->publicUrl().'#'.$this->tourAnchor($tour['name']);
    }

    public function tourEnquiryUrl(string $tourName): string
    {
        return $this->whatsappUrl().'?text='.rawurlencode("Hi, I'd like to ask about ".Str::lower($this->label())." to {$tourName}.");
    }

    /**
     * Uploaded images and videos owned by this page, excluding bundled and remote files.
     *
     * @return array<int, string>
     */
    public function storedImagePaths(): array
    {
        return array_values(array_unique(array_filter(
            [$this->hero_image, $this->hero_video, ...array_column($this->tours ?? [], 'image')],
            static fn (?string $path): bool => filled($path) && ! str_starts_with($path, 'images/') && ! preg_match('#^https?://#', $path),
        )));
    }
}
