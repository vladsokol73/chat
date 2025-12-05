<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyErpJwt
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);

        $config = config('services.erp_jwt');
        $secret = $config['secret'] ?? null;
        $algo = $config['algo'] ?? 'HS256';
        $issuer = $config['issuer'] ?? null;
        $audience = $config['audience'] ?? null;
        $leeway = (int) ($config['leeway'] ?? 0);

        if (! $secret) {
            return response()->json(['message' => 'JWT not configured'], 500);
        }

        \Firebase\JWT\JWT::$leeway = $leeway;

        try {
            $decoded = JWT::decode($token, new Key($secret, $algo));

            if ($issuer && ($decoded->iss ?? null) !== $issuer) {
                return response()->json(['message' => 'Invalid token issuer'], 401);
            }
            if ($audience && ($decoded->aud ?? null) !== $audience) {
                return response()->json(['message' => 'Invalid token audience'], 401);
            }

            // Пробросим полезные данные в запрос
            $request->attributes->set('erp_jwt', $decoded);

            return $next($request);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
    }
}
