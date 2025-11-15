<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // La clave configurada en .env
        $internalKey = env('INTERNAL_API_KEY');

        // La clave enviada en la cabecera de la petición
        $headerKey = $request->header('X-Internal-Key');

        // Verificar si la clave coincide
        if (!$headerKey || $headerKey !== $internalKey) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        return $next($request);
    }
}
