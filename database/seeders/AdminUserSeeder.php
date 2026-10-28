<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('admin.name');
        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($name) || trim($name) === '') {
            throw new InvalidArgumentException('Set ADMIN_NAME to a non-empty administrator name.');
        }

        if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Set ADMIN_EMAIL to a valid administrator email address.');
        }

        if (! is_string($password) || $password === '') {
            throw new InvalidArgumentException('Set ADMIN_PASSWORD to a non-empty password.');
        }

        if (app()->isProduction() && strlen($password) < 12) {
            throw new InvalidArgumentException('Set ADMIN_PASSWORD to a password of at least 12 characters in production.');
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => $password,
            'is_admin' => true,
        ])->save();
    }
}
