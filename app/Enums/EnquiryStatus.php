<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In progress',
            self::Closed => 'Closed',
        };
    }

    /**
     * The admin status badge style.
     */
    public function badge(): string
    {
        return match ($this) {
            self::New => 'pending',
            self::InProgress => 'progress',
            self::Closed => 'completed',
        };
    }

    /**
     * How the status reads to the client who sent the request.
     */
    public function clientLabel(bool $isBooked): string
    {
        return match ($this) {
            self::New => 'Received',
            self::InProgress => 'Being planned',
            self::Closed => $isBooked ? 'Booked' : 'Closed',
        };
    }

    /**
     * Button text for moving an enquiry into this status.
     */
    public function actionLabel(): string
    {
        return match ($this) {
            self::New => 'Mark as new',
            self::InProgress => 'Mark in progress',
            self::Closed => 'Close',
        };
    }

    /**
     * New and in-progress enquiries still need a reply.
     *
     * @return array<int, string>
     */
    public static function pendingValues(): array
    {
        return [self::New->value, self::InProgress->value];
    }
}
