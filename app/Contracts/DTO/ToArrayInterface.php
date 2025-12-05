<?php

namespace App\Contracts\DTO;

interface ToArrayInterface
{
    /**
     * Преобразовать DTO в массив
     *
     * @return array Массив данных
     */
    public function toArray(): array;
}
