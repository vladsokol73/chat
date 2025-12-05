<?php

namespace App\Contracts\DTO;

use Illuminate\Database\Eloquent\Model;

interface FromModelInterface
{
    /**
     * Создать DTO из модели
     *
     * @param  Model  $model  Модель
     * @return static Экземпляр DTO
     */
    public static function fromModel(Model $model): static;
}
