<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware globaux (appliqués à toutes les routes web)
        $middleware->web([
            \App\Http\Middleware\EncryptCookies::class,
        ]);

        $middleware->statefulApi();

        // Middleware pour les routes API
        $middleware->api([
            \Illuminate\Http\Middleware\HandleCors::class, // CORS natif Laravel
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Enregistrement des middlewares personnalisés
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'admin' => \App\Http\Middleware\EnsureIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();