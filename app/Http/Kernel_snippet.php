<?php
/**
 * KERNEL REGISTRATION SNIPPET
 * In app/Http/Kernel.php, add 'admin' to $middlewareAliases:
 *
 *   protected $middlewareAliases = [
 *       ...
 *       'admin' => \App\Http\Middleware\AdminMiddleware::class,
 *   ];
 *
 * ─── OR in Laravel 11+ (bootstrap/app.php) ───────────────────────────────
 * use App\Http\Middleware\AdminMiddleware;
 *
 * ->withMiddleware(function (Middleware $middleware) {
 *     $middleware->alias([
 *         'admin' => AdminMiddleware::class,
 *     ]);
 * })
 */
