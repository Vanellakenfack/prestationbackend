<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->type !== 'admin') {
            return response()->json(['message' => 'Accès réservé aux administrateurs'], 403);
        }
        return $next($request);
    }
}