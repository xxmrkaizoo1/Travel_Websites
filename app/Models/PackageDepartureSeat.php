<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Seats offered on one departure of a series package. The departure is found by its key,
 * made from the destination name and the departure date (see BookablePackage::departureKey()).
 */
class PackageDepartureSeat extends Model
{
    protected $fillable = [
        'departure_key',
        'seats',
    ];

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<TourPackagePage, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPackagePage::class, 'tour_package_page_id');
    }
}
