<?php

namespace App\Enums;

enum EnquirySource: string
{
    case Website = 'website';
    case WhatsApp = 'whatsapp';
    case Phone = 'phone';
    case Email = 'email';
    case WalkIn = 'walk_in';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'Website',
            self::WhatsApp => 'WhatsApp',
            self::Phone => 'Phone call',
            self::Email => 'Email',
            self::WalkIn => 'Walk-in',
        };
    }
}
