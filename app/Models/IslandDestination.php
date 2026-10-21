<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IslandDestination extends Model
{
    protected $fillable = [
        'slug',
        'position',
        'name',
        'country',
        'description',
        'known_as',
        'perfect_for',
        'benefits_heading',
        'benefits',
        'feature_image',
        'feature_image_alt',
        'feature_caption',
        'gallery',
    ];

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'gallery' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function imageUrl(string $path): string
    {
        $scheme = parse_url($path, PHP_URL_SCHEME);

        if (in_array($scheme, ['http', 'https'], true)) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
