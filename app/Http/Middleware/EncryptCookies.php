<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * Disable ALL cookie encryption temporarily to fix MAC error.
     */
    protected function shouldEncrypt($name)
    {
        return false;
    }
}