<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MidtransSnapCsp
{
    /**
     * Allowed origins for Midtrans Snap (sandbox + production).
     * 'unsafe-eval' is required by Snap.js internally.
     */
    private const SCRIPT_SRC = [
        "'self'",
        "'unsafe-inline'",
        "'unsafe-eval'",
        'https://app.sandbox.midtrans.com',
        'https://app.midtrans.com',
        'https://snap-assets.al-pc-id-b.cdn.gtflabs.io',
        'https://snap-assets.midtrans.com',
        'https://api.sandbox.midtrans.com',
        'https://api.midtrans.com',
        'https://pay.google.com',
        'https://gwk.gopayapi.com',
        'https://js.braintreegateway.com',
        'https://cdn.gtflabs.io',
    ];

    private const FRAME_SRC = [
        "'self'",
        'https://app.sandbox.midtrans.com',
        'https://app.midtrans.com',
        'https://pay.google.com',
        'https://gwk.gopayapi.com',
        'https://simulator.sandbox.midtrans.com',
    ];

    private const CONNECT_SRC = [
        "'self'",
        'https://api.sandbox.midtrans.com',
        'https://api.midtrans.com',
        'https://app.sandbox.midtrans.com',
        'https://app.midtrans.com',
    ];

    private const IMG_SRC = [
        "'self'",
        'data:',
        'https:',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = implode('; ', [
            'script-src '  . implode(' ', self::SCRIPT_SRC),
            'frame-src '   . implode(' ', self::FRAME_SRC),
            'connect-src ' . implode(' ', self::CONNECT_SRC),
            'img-src '     . implode(' ', self::IMG_SRC),
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "default-src 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        // Remove any pre-existing CSP set by other middleware so ours wins
        // (comment this out if you want to keep your app-wide CSP elsewhere)
        $response->headers->remove('X-Content-Security-Policy');

        return $response;
    }
}