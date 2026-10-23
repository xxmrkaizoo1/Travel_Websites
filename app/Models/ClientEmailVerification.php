<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Hidden(['password', 'otp_hash'])]
class ClientEmailVerification extends Model
{
    protected $fillable = [
        'email',
        'name',
        'password',
        'otp_hash',
        'phone',
        'expires_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
