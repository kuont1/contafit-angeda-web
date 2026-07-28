<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si ya hay un usuario autenticado (ej. $this->actingAs($user) en pruebas de PHPUnit)
        if ($request->user() || auth()->check()) {
            return $next($request);
        }

        $header = $request->header('Authorization');

        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Token JWT de autenticación no proporcionado.',
            ], 401);
        }

        $token = substr($header, 7);
        $payload = JwtService::validateToken($token);

        if (! $payload || ! isset($payload->sub)) {
            // Fallback a Sanctum PersonalAccessToken
            $pat = PersonalAccessToken::findToken($token);
            if ($pat && $pat->tokenable) {
                auth()->setUser($pat->tokenable);
                $request->setUserResolver(fn () => $pat->tokenable);

                return $next($request);
            }

            return response()->json([
                'success' => false,
                'message' => 'Token JWT no válido o expirado.',
            ], 401);
        }

        $user = User::find($payload->sub);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado para este token JWT.',
            ], 401);
        }

        auth()->setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
