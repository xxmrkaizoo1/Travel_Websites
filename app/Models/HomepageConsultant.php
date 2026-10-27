<?php

namespace App\Models;

use Database\Factories\HomepageConsultantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomepageConsultant extends Model
{
    /** @use HasFactory<HomepageConsultantFactory> */
    use HasFactory;

    public const MAX_CONSULTANTS = 16;

    /** The main Smart Muslim WhatsApp line, used for consultants without their own number. */
    public const MAIN_WHATSAPP = '60165500590';

    protected $fillable = ['position', 'name', 'role', 'whatsapp', 'photo_path'];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /**
     * Bundled photos live under public/images; uploaded ones are on the public disk.
     */
    public function photoUrl(): ?string
    {
        if (blank($this->photo_path)) {
            return null;
        }

        return str_starts_with($this->photo_path, 'images/') ? asset($this->photo_path) : Storage::disk('public')->url($this->photo_path);
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }

    /**
     * Opens a chat with this consultant, or with the main line (naming the consultant) when they have no number.
     */
    public function whatsappUrl(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->whatsapp);

        if ($digits !== '') {
            $number = str_starts_with($digits, '0') ? '6'.$digits : $digits;

            return "https://wa.me/{$number}?text=".rawurlencode("Hi {$this->name}, I have a question about Smart Muslim tours.");
        }

        return 'https://wa.me/'.self::MAIN_WHATSAPP.'?text='.rawurlencode("Hi, I would like to speak to {$this->name} about Smart Muslim tours.");
    }

    public function hasOwnWhatsapp(): bool
    {
        return preg_replace('/\D/', '', (string) $this->whatsapp) !== '';
    }
}
