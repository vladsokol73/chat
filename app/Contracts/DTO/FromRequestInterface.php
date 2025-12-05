<?php

namespace App\Contracts\DTO;

use Illuminate\Http\Request;

interface FromRequestInterface
{
    /**
     * Создать DTO из HTTP-запроса
     *
     * @param  Request  $request  HTTP-запрос
     * @return static Экземпляр DTO
     */
    public static function fromRequest(Request $request): static;
}
