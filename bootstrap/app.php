<?php
/**
 * bootstrap/app.php — FULL FILE for Laravel 11
 * Replace your existing bootstrap/app.php with this.
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\MidtransSnapCsp;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'midtrans.csp' => MidtransSnapCsp::class,
        ]);

        // Exempt IPaymu and Midtrans webhooks from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'checkout/ipaymu/webhook',
            'checkout/midtrans/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
