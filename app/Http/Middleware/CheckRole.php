<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            // Si no está logueado
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $user = Auth::user();

        foreach ($roles as $role) {
            if ($user->role === $role) {
                // Si tiene el rol, déjalo pasar
                return $next($request);
            }
        }

        // Si no tiene el rol
        return response()->json(['message' => 'No autorizado. Se requiere rol de administrador.'], 403);
    }
}