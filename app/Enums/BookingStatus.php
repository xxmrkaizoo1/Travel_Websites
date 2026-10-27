<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Confirmed and completed bookings count towards revenue.
     */
    public function earnsRevenue(): bool
    {
        return in_array($this, [self::Confirmed, self::Completed], true);
    }

    /**
     * @return array<int, string>
     */
    public static function revenueValues(): array
    {
        return [self::Confirmed->value, self::Completed->value];
    }
}
