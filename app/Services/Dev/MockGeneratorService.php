<?php

namespace App\Services\Dev;

class MockGeneratorService
{
    public function randomClient(): array
    {
        $names = ['Alice', 'Bob', 'Charlie', 'Diana', 'Eve', 'Frank'];

        return [
            'name' => $names[array_rand($names)].' #'.rand(100, 999),
            'avatar' => null,
        ];
    }

    public function randomMessage(): string
    {
        $samples = [
            'Привет! Это тестовое сообщение ',
            'Я люблю чатиться с вами в этом чате! ',
            'Мне нравится, что я могу отправлять столько сообщений подряд?',
            'Вот это сообщение уже отправлено с помощью задачи! ',
            'А может быть, что вы хотите пообщаться со мной?',
            'Может быть, что вы хотите узнать, как я могу отправлять так много сообщений?',
            'Mock ping message...',
            'Это просто шум для нагрузки ',
            'Я не знаю, что писать. Помогите мне! ',
            'Вот это мое последнее сообщение... ',
            'Или нет? ',
        ];

        return $samples[array_rand($samples)];
    }
}
