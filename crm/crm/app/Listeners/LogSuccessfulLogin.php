<?php

namespace App\Listeners;

use App\Models\AccessLog;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        AccessLog::create([
            'user_id'      => $user->id ?? null,
            'email'        => $user->email ?? null,
            'ip_address'   => request()->ip(),
            'user_agent'   => substr((string) request()->userAgent(), 255),
            'route'        => 'login',
            'logged_in_at' => now(),
        ]);
    }
}
