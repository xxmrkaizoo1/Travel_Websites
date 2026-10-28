<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomepageService extends Model
{
    protected $fillable = [
        'title',
        'overlay_label',
        'button_label',
        'href',
        'image_path',
        'image_alt',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function imageUrl(): string
    {
        $scheme = parse_url($this->image_path, PHP_URL_SCHEME);

        if (in_array($scheme, ['http', 'https'], true)) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, 'images/')) {
            return asset($this->image_path);
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
