<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'internal.auth' => \App\Http\Middleware\InternalAuthMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // 🕒 Programamos el comando cada 5 minutos
        $schedule->command('sync:process-node_electronics')->everyFiveMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
