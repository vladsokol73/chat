<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginAsOneController extends Controller
{
    /**
     * Dev-only: авторизовать одного пользователя из БД по email.
     * - Берёт email из DEV_USER_EMAIL или из тела запроса.
     * - Если пользователя нет — возвращает 404.
     * - Логинит через сессионную авторизацию и возвращает ok.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Защитимся от случайного вызова не в локальной среде
        if (! app()->isLocal()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // email из env или запроса
        $email = (string) $request->input('email', '');

        if ($email === '') {
            return ApiResponse::badRequest('Не указан email');
        }

        // Поиск пользователя по email без авто-создания
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return ApiResponse::notFound('Пользователь с таким email не найден');
        }

        // Авторизация через сессию (cookie)
        Auth::login($user, true);

        return ApiResponse::successMessage('ok');
    }
}
