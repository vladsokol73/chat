<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TranslateRequest;
use App\Http\Responses\ApiResponse;
use App\Services\TranslatorService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use OpenApi\Attributes as OA;
use RuntimeException;
use Throwable;

class TranslatorController extends Controller
{
    public function __construct(
        private readonly TranslatorService $translatorService,
    ) {}

    #[OA\Get(
        path: '/api/translate',
        operationId: 'translate',
        summary: 'Перевести текст',
        tags: ['Translator'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/TranslateRequest_LangParam'),
            new OA\Parameter(ref: '#/components/parameters/TranslateRequest_TextParam'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Translation successful',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    properties: [
                                        new OA\Property(
                                            property: 'translation',
                                            type: 'string',
                                            example: 'Translated text'
                                        ),
                                    ],
                                    type: 'object'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/BadRequestResponse', response: 400),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function translate(TranslateRequest $request)
    {
        try {
            $lang = $request->validated('lang');
            $text = $request->validated('text');

            $translation = $this->translatorService->translate($lang, $text);

            return ApiResponse::success(
                data: ['translation' => $translation],
                message: 'Translation successful'
            );
        } catch (RequestException|ConnectionException $e) {
            return ApiResponse::serverError('Translator API error: '.$e->getMessage());
        } catch (RuntimeException $e) {
            return ApiResponse::badRequest($e->getMessage());
        } catch (Throwable $e) {
            return ApiResponse::serverError('Unexpected error: '.$e->getMessage());
        }
    }
}
