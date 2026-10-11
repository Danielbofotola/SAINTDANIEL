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
    ->withMiddleware(function (Middleware $middleware): void {
        // LocalTunnel (et tout reverse-proxy) termine le TLS en amont.
        // Sans ceci, Laravel croit être en HTTP et génère des URL
        // http://127.0.0.1:8000, bloquées par le navigateur (mixed content).
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureUserIsActive::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\FlashGlobal::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\AccessMessagerie::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
