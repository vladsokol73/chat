<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ErpSsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use OpenApi\Attributes as OA;
use Throwable;

class SsoExchangeController extends Controller
{
    public function __construct(private readonly ErpSsoService $ssoService) {}

    #[OA\Post(
        path: '/api/auth/sso-exchange',
        operationId: 'ssoExchange',
        summary: 'Обмен ERP JWT на сессию',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', properties: [
                                new OA\Property(property: 'user', properties: [
                                    new OA\Property(property: 'id', type: 'string'),
                                    new OA\Property(property: 'name', type: 'string'),
                                    new OA\Property(property: 'email', type: 'string'),
                                    new OA\Property(property: 'avatar', type: 'string', nullable: true),
                                ], type: 'object'),
                            ], type: 'object'),
                        ], type: 'object'),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/BadRequestResponse', response: 400),
            new OA\Response(ref: '#/components/responses/UnauthorizedResponse', response: 401),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        // erp.jwt middleware уже провалидировал токен и положил 'erp_jwt' в атрибуты
        $decoded = $request->attributes->get('erp_jwt');
        if (! $decoded) {
            return ApiResponse::unauthorized('Invalid token');
        }
        try {
            $user = $this->ssoService->exchangeToSession($decoded);

            return ApiResponse::success(['user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
            ]]);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::badRequest($e->getMessage());
        } catch (Throwable) {
            return ApiResponse::serverError('SSO exchange failed');
        }
    }
}
