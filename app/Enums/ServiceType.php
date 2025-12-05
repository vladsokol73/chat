<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ServiceType',
    description: 'Type of integration service',
    type: 'string',
    enum: ['telegram']
)]
enum ServiceType: string
{
    case TELEGRAM = 'telegram';

    public const array VALUES = ['telegram'];

    /**
     * Получить все значения enum в виде массива
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Получить все case'ы с метаданными для фронта
     */
    public static function toArray(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
        ], self::cases());
    }

    /**
     * Получить читаемое название
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::TELEGRAM => 'Telegram',
        };
    }
}
