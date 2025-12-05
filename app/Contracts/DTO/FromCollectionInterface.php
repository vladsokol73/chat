<?php

namespace App\Contracts\DTO;

use Illuminate\Support\Collection;

interface FromCollectionInterface
{
    /**
     * Создать массив DTO из коллекции моделей
     *
     * @param  Collection  $collection  Коллекция моделей
     * @return array Массив DTO
     */
    public static function fromCollection(Collection $collection): array;
}
