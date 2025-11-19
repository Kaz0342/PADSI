<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AuthCheck; // <-- Middleware lo yang krusial
use App\Http\Middleware\RoleCheck; // <-- Middleware lo yang krusial

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // Pendaftaran ROUTE MIDDLEWARE untuk AuthCheck dan RoleCheck
        $middleware->alias([
            'authcheck' => AuthCheck::class,
            'role' => RoleCheck::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();