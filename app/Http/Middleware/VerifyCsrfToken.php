<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{


    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/transfer/callback',
        '/transfer/callback/*',
        '/transfer/success/*',
        '/transfer/success/',
        '/transfer/fail/',
        '/transfer/fail/*',
        '/api',
        '/api/*'
    ];
}
