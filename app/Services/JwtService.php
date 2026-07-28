<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class JwtService
{
    private static function getSecretKey(): string
    {
        $key = config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return $key ?: 'default_jwt_secret_key_contafit_agenda_2026';
    }

    /**
     * Genera un token JWT (RFC 7519) firmado para el usuario.
     */
    public static function generateToken(User $user, int $ttlMinutes = 1440): string
    {
        $issuedAt = time();
        $expireAt = $issuedAt + ($ttlMinutes * 60);

        $payload = [
            'iss' => config('app.url', 'http://127.0.0.1:8000'),
            'sub' => $user->id,
            'iat' => $issuedAt,
            'exp' => $expireAt,
            'email' => $user->email,
        ];

        return JWT::encode($payload, self::getSecretKey(), 'HS256');
    }

    /**
     * Valida y decodifica el token JWT. Retorna el payload o null si es inválido/expirado.
     */
    public static function validateToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key(self::getSecretKey(), 'HS256'));
        } catch (Throwable $e) {
            return null;
        }
    }
}
