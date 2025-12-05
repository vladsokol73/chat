<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthSessionController extends Controller
{
    #[OA\Get(
        path: '/api/me',
        operationId: 'me',
        summary: 'Текущий пользователь',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User or guest',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', properties: [
                                new OA\Property(property: 'authenticated', type: 'boolean'),
                                new OA\Property(property: 'user', nullable: true),
                            ], type: 'object'),
                        ], type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function me(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::success(['authenticated' => false]);
        }

        return ApiResponse::success([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/auth/logout',
        operationId: 'logout',
        summary: 'Выход из сессии',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                ])
            ),
        ]
    )]
    public function logout(): JsonResponse
    {
        Auth::logout();

        return ApiResponse::successMessage('ok');
    }
}
