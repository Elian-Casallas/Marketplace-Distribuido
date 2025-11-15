<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-Internal-Key');
        if (!$key || $key !== env('INTERNAL_API_KEY_MAIN')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return $next($request);
    }
}
