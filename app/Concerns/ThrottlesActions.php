<?php

namespace App\Concerns;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

trait ThrottlesActions
{
    /** Limite une action par utilisateur connecté, ou par IP pour un visiteur. */
    protected function throttle(string $action, int $maxAttempts, int $decaySeconds, string $field): void
    {
        $key = $action.'|'.(auth()->id() ?? request()->ip());

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                $field => 'Trop de tentatives. Réessaie dans '.RateLimiter::availableIn($key).' secondes.',
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);
    }
}