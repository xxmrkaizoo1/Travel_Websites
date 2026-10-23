<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartMuslimMenu extends Model
{
    public const ICONS = [
        'compass' => 'Compass',
        'mosque' => 'Mosque',
        'kaaba' => 'Kaaba',
        'crescent' => 'Crescent',
        'plane' => 'Air travel',
        'globe' => 'World',
        'beach' => 'Beach',
        'luggage' => 'Luggage',
        'ship' => 'Cruise',
        'hotel' => 'Hotel',
        'star' => 'Featured',
        'shopping' => 'Shopping',
    ];

    protected $fillable = [
        'parent_id',
        'label',
        'icon',
        'position',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(TourPackagePage::class)->orderBy('name');
    }
}
