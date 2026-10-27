<?php

namespace App\Models;

use App\Enums\EnquirySource;
use App\Enums\EnquiryStatus;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'destination',
        'message',
        'source',
        'status',
    ];

    protected $attributes = [
        'status' => 'new',
        'source' => 'website',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnquiryStatus::class,
            'source' => EnquirySource::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->whereIn('status', EnquiryStatus::pendingValues());
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

            $query->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('destination', 'like', $like)
                ->orWhere('message', 'like', $like);
        });
    }
}
