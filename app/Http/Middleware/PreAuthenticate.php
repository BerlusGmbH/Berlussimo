<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class PreAuthenticate extends Middleware
{
    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $guards
     * @return void
     *
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                $this->auth->shouldUse($guard);
                return;
            }
        }
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
    }
}