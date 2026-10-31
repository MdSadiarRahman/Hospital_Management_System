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
        $middleware->alias([
            'staff' => \App\Http\Middleware\EnsureStaff::class,
            'patient' => \App\Http\Middleware\EnsurePatient::class,
            'patient.linked' => \App\Http\Middleware\EnsurePatientLinked::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            return $request->is('patient*')
                ? route('patient.login')
                : route('portal.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
