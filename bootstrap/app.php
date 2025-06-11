<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',  // Assurez-vous d'avoir cette ligne
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware globaux (appliqués à toutes les routes)
        $middleware->web([
            \App\Http\Middleware\EncryptCookies::class,
       
        ]);
        $middleware->statefulApi();

        $middleware->api([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        // Remplacez la ligne suivante :
        // \Illuminate\Routing\Middleware\ThrottleRequests::class.':api'
        // Par :
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1'
        ]);

        // Enregistrement du middleware personnalisé
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
           'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,        ]);

        // Vous pouvez aussi ajouter des middlewares à des groupes spécifiques
        // $middleware->appendToGroup('web', \App\Http\Middleware\CustomWebMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();