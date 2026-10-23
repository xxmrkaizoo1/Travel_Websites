<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiceClient extends Model
{
    protected $fillable = ['name', 'logo', 'position'];

    public function logoUrl(): string
    {
        return MicePage::resolveImageUrl($this->logo);
    }
}
