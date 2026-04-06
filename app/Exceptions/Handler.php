<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Contracts\Encryption\DecryptException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Global fix for "The MAC is invalid" error
        $this->renderable(function (DecryptException $e) {
            return redirect()->to('/admin/crm/communication-hub')
                ->withCookie(cookie()->forget(config('session.cookie')))
                ->header('Clear-Site-Data', '"cookies"');
        });
    }
}
