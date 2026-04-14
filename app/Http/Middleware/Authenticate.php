<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        if (str_contains($request->url(), '/portal') || str_contains($request->url(), '/client')) {
            return route('portal.login');
        }

        if ($request->is('customer/*') || $request->is('customer')) {
            return route('psp.login');
        }

        if ($request->is('m/*') || $request->is('m')) {
            return route('mobile.login');
        }

        return route('login');
    }
}
