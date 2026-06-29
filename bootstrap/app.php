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
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn () => route('filament.admin.auth.login'));
        // iOS Safari'ning "native" POST /admin/login so'rovi (tana bo'sh, token yo'q)
        // CSRF tekshiruvida 419 bermasligi uchun — quyidagi redirect marshruti uchun istisno.
        $middleware->validateCsrfTokens(except: ['admin/login']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
