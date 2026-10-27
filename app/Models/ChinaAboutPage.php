<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ChinaAboutPage extends Model
{
    public const MAX_TRAVELLER_PHOTOS = 24;

    protected $fillable = ['badge', 'title', 'intro', 'facts', 'body', 'note', 'gallery', 'traveller_photos'];

    protected function casts(): array
    {
        return [
            'facts' => 'array',
            'gallery' => 'array',
            'traveller_photos' => 'array',
        ];
    }

    /**
     * Photos shipped with the site live under public/images; uploaded ones are on the public disk.
     */
    public function photoUrl(string $path): string
    {
        return str_starts_with($path, 'images/') ? asset($path) : Storage::disk('public')->url($path);
    }
}
