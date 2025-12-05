<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    // --------------------
    // HTTP Status Codes
    // --------------------
    public const HTTP_OK = 200;

    public const HTTP_CREATED = 201;

    public const HTTP_ACCEPTED = 202;

    public const HTTP_NO_CONTENT = 204;

    public const HTTP_BAD_REQUEST = 400;

    public const HTTP_UNAUTHORIZED = 401;

    public const HTTP_FORBIDDEN = 403;

    public const HTTP_NOT_FOUND = 404;

    public const HTTP_METHOD_NOT_ALLOWED = 405;

    public const HTTP_NOT_ACCEPTABLE = 406;

    public const HTTP_REQUEST_TIMEOUT = 408;

    public const HTTP_CONFLICT = 409;

    public const HTTP_GONE = 410;

    public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;

    public const HTTP_I_AM_A_TEAPOT = 418;

    public const HTTP_UNPROCESSABLE_ENTITY = 422;

    public const HTTP_TOO_MANY_REQUESTS = 429;

    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    public const HTTP_NOT_IMPLEMENTED = 501;

    public const HTTP_BAD_GATEWAY = 502;

    public const HTTP_SERVICE_UNAVAILABLE = 503;

    public const HTTP_GATEWAY_TIMEOUT = 504;

    public const HTTP_INSUFFICIENT_STORAGE = 507;

    // --------------------
    // HTTP Messages
    // --------------------
    private const HTTP_MESSAGES = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        415 => 'Unsupported Media Type',
        418 => "I'm a teapot",
        422 => 'Validation Error',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        507 => 'Insufficient Storage',
    ];

    // --------------------
    // Базовый метод
    // --------------------
    public static function send(
        bool $success = true,
        mixed $data = null,
        ?string $message = null,
        ?array $errors = null,
        ?array $meta = null,
        int $code = self::HTTP_OK
    ): JsonResponse {
        $message ??= self::HTTP_MESSAGES[$code] ?? 'Unknown Status';

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
            'meta' => $meta,
        ], $code);
    }

    // --------------------
    // Успешные ответы
    // --------------------
    public static function success(
        mixed $data = null,
        ?string $message = null,
        ?array $meta = null,
        int $code = self::HTTP_OK
    ): JsonResponse {
        return self::send(true, $data, $message, null, $meta, $code);
    }

    public static function successMessage(
        ?string $message = null,
        ?array $meta = null,
        int $code = self::HTTP_OK
    ): JsonResponse {
        return self::send(true, null, $message, null, $meta, $code);
    }

    public static function created(mixed $data = null, ?string $message = null): JsonResponse
    {
        return self::success($data, $message ?? self::HTTP_MESSAGES[self::HTTP_CREATED], null, self::HTTP_CREATED);
    }

    public static function updated(mixed $data = null, ?string $message = 'Resource updated successfully'): JsonResponse
    {
        return self::success($data, $message);
    }

    public static function deleted(?string $message = 'Resource deleted successfully'): JsonResponse
    {
        return self::successMessage($message);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(null, self::HTTP_NO_CONTENT);
    }

    // --------------------
    // Ошибки
    // --------------------
    public static function error(?string $message = null, ?array $errors = null, int $code = self::HTTP_BAD_REQUEST): JsonResponse
    {
        return self::send(false, null, $message, $errors, null, $code);
    }

    public static function validation(array $errors, ?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_UNPROCESSABLE_ENTITY], $errors, self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function validationMessage(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_UNPROCESSABLE_ENTITY], null, self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function formError(array $errors, ?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_BAD_REQUEST], $errors, self::HTTP_BAD_REQUEST);
    }

    public static function unauthorized(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_UNAUTHORIZED], null, self::HTTP_UNAUTHORIZED);
    }

    public static function forbidden(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_FORBIDDEN], null, self::HTTP_FORBIDDEN);
    }

    public static function notFound(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_NOT_FOUND], null, self::HTTP_NOT_FOUND);
    }

    public static function serverError(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_INTERNAL_SERVER_ERROR], null, self::HTTP_INTERNAL_SERVER_ERROR);
    }

    public static function badRequest(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_BAD_REQUEST], null, self::HTTP_BAD_REQUEST);
    }

    public static function methodNotAllowed(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_METHOD_NOT_ALLOWED], null, self::HTTP_METHOD_NOT_ALLOWED);
    }

    public static function notAcceptable(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_NOT_ACCEPTABLE], null, self::HTTP_NOT_ACCEPTABLE);
    }

    public static function requestTimeout(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_REQUEST_TIMEOUT], null, self::HTTP_REQUEST_TIMEOUT);
    }

    public static function conflict(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_CONFLICT], null, self::HTTP_CONFLICT);
    }

    public static function gone(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_GONE], null, self::HTTP_GONE);
    }

    public static function unsupportedMediaType(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_UNSUPPORTED_MEDIA_TYPE], null, self::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    public static function tooManyRequests(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_TOO_MANY_REQUESTS], null, self::HTTP_TOO_MANY_REQUESTS);
    }

    public static function notImplemented(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_NOT_IMPLEMENTED], null, self::HTTP_NOT_IMPLEMENTED);
    }

    public static function badGateway(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_BAD_GATEWAY], null, self::HTTP_BAD_GATEWAY);
    }

    public static function serviceUnavailable(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_SERVICE_UNAVAILABLE], null, self::HTTP_SERVICE_UNAVAILABLE);
    }

    public static function gatewayTimeout(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_GATEWAY_TIMEOUT], null, self::HTTP_GATEWAY_TIMEOUT);
    }

    public static function insufficientStorage(?string $message = null): JsonResponse
    {
        return self::error($message ?? self::HTTP_MESSAGES[self::HTTP_INSUFFICIENT_STORAGE], null, self::HTTP_INSUFFICIENT_STORAGE);
    }
}
