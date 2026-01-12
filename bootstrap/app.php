<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
   ->withMiddleware(function (Middleware $middleware) {
        // Middleware Alias untuk Admin
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);

        // Middleware Web (Jalan di semua halaman website)
        $middleware->web(
            prepend: [\App\Http\Middleware\AdminSessionCookie::class],
            append: [
                \App\Http\Middleware\Localization::class,
                \App\Http\Middleware\SecurityHeaders::class,
            ]
        );

        $middleware->prependToPriorityList(
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\AdminSessionCookie::class
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
