<?php

namespace App\Contracts\DTO;

interface FromArrayInterface
{
    /**
     * Создать DTO из массива данных
     *
     * @param  array  $data  Массив данных
     * @return static Экземпляр DTO
     */
    public static function fromArray(array $data): static;
}
