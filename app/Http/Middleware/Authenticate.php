<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // === Khusus halaman cart: arahkan ke Home ===
            if ($request->is('cart') || $request->is('cart/*')) {
                // pakai route('home') kalau ada, atau url('/')
                return route('home'); // atau: return url('/');
            }
            
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
