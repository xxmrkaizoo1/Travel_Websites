<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChinaAboutPage extends Model
{
    protected $fillable = ['badge', 'title', 'intro', 'facts', 'body', 'note', 'gallery'];

    protected function casts(): array
    {
        return [
            'facts' => 'array',
            'gallery' => 'array',
        ];
    }
}
