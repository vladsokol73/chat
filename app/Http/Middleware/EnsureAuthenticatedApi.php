<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticatedApi
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return ApiResponse::unauthorized('Unauthenticated');
        }

        return $next($request);
    }
}
