<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiceGalleryImage extends Model
{
    protected $fillable = ['image', 'caption', 'position'];

    public function imageUrl(): string
    {
        return MicePage::resolveImageUrl($this->image);
    }
}
