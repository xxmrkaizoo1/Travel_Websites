<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Support\BookablePackage;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'destination',
        'package_name',
        'travel_date',
        'return_date',
        'adults',
        'children',
        'amount',
        'status',
        'notes',
        'special_requests',
        'bookable_option',
    ];

    protected $attributes = [
        'status' => 'pending',
        'children' => 0,
    ];

    protected function casts(): array
    {
        return [
            'travel_date' => 'date',
            'return_date' => 'date',
            'adults' => 'integer',
            'children' => 'integer',
            'amount' => 'decimal:2',
            'status' => BookingStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Booking $booking): void {
            $booking->forceFill(['reference' => sprintf('BK-%s-%04d', $booking->created_at->format('Y'), $booking->id)])->saveQuietly();
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The enquiry this booking was converted from, if any.
     *
     * @return HasOne<Enquiry, $this>
     */
    public function enquiry(): HasOne
    {
        return $this->hasOne(Enquiry::class);
    }

    /**
     * The Smart Muslim package a client booked online, if any.
     *
     * @return MorphTo<Model, $this>
     */
    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    public function wasBookedOnline(): bool
    {
        return $this->bookable_type !== null;
    }

    #[Scope]
    protected function earningRevenue(Builder $query): void
    {
        $query->whereIn('status', BookingStatus::revenueValues());
    }

    /**
     * Trips that have not started yet and are still going ahead.
     */
    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->whereDate('travel_date', '>=', today())
            ->where('status', '!=', BookingStatus::Cancelled->value);
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term): void {
            $like = '%'.$term.'%';

            $query->where('reference', 'like', $like)
                ->orWhere('destination', 'like', $like)
                ->orWhere('package_name', 'like', $like)
                ->orWhereHas('client', fn (Builder $client) => $client->where('name', 'like', $like)->orWhere('email', 'like', $like));
        });
    }

    /**
     * Whole days from today until departure (0 on the travel day, negative once it has passed).
     */
    public function daysUntilTravel(): int
    {
        return (int) today()->diffInDays($this->travel_date, false);
    }

    /**
     * Why this booking cannot move to $status with $travellers on it, or null when it can. A confirmed or
     * completed online booking takes one seat per traveller on its package (or series departure).
     */
    public function seatProblem(BookingStatus $status, int $travellers): ?string
    {
        if ($this->bookable === null || ! $status->earnsRevenue()) {
            return null;
        }

        $bookable = new BookablePackage($this->bookable);
        $seatsLeft = $bookable->seatsLeft($this->bookable_option);

        if ($seatsLeft === null) {
            return null;
        }

        // Seats this booking already holds become free again before it is counted with its new details.
        $available = $seatsLeft + ($this->status->earnsRevenue() ? $this->adults + $this->children : 0);
        $place = $bookable->hasSeatsPerDeparture() ? 'departure' : 'package';

        if ($travellers <= $available) {
            return null;
        }

        return $available === 0
            ? "This {$place} is fully booked, so the booking cannot be confirmed. Raise the available seats on the Online booking page first."
            : "Only {$available} ".Str::plural('seat', $available)." available on this {$place}, but this booking has {$travellers} travellers. Raise the available seats on the Online booking page or reduce the travellers.";
    }

    public function guestsLabel(): string
    {
        $parts = [$this->adults.' '.($this->adults === 1 ? 'Adult' : 'Adults')];

        if ($this->children > 0) {
            $parts[] = $this->children.' '.($this->children === 1 ? 'Child' : 'Children');
        }

        return implode(', ', $parts);
    }
}
