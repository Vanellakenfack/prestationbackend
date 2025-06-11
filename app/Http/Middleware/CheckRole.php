<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class CheckRole
{
    // app/Http/Middleware/CheckRole.php
public function handle($request, Closure $next, ...$roles) {
    if (!in_array($request->user()->type, $roles)) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    return $next($request);
}
}
