<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\TwoFactorCode;

class TwoFactorService
{
    public function send(User $user): string
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ])->save();

        $user->notify(new TwoFactorCode($code));

        return $code;
    }

    public function validate(User $user, string $code): bool
    {
        if (! $user->two_factor_code || ! $user->two_factor_expires_at) {
            return false;
        }

        if ($user->two_factor_expires_at->isPast()) {
            return false;
        }

        return hash_equals($user->two_factor_code, $code);
    }
}