<?php

namespace App\Services;

use App\DTO\Dify\DifyFileDto;
use App\DTO\Dify\DifyParsedTextsDto;
use App\DTO\Dify\DifyResponseDto;
use App\Enums\Dify\DifyResponseMode;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use stdClass;

class DifyService
{
    private string $baseUrl = 'https://dify.investingindigital.com';

    /**
     * @param  array<int, DifyFileDto>  $files
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function sendMessage(
        string $apiKey,
        string $query,
        string $userId,
        ?string $conversationId = null,
        array $files = []
    ): DifyParsedTextsDto {
        $response = Http::withToken($apiKey)
            ->timeout(120)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl.'/v1/chat-messages', [
                'query' => $query !== '' ? $query : ' ',
                'inputs' => new stdClass,
                'response_mode' => DifyResponseMode::BLOCKING->value,
                'user' => $userId,
                'conversation_id' => $conversationId ?? '',
                'files' => array_map(
                    fn (DifyFileDto $file) => $file->toArray(),
                    $files
                ),
            ])
            ->throw();

        $dto = DifyResponseDto::fromArray($response->json());

        return new DifyParsedTextsDto(
            texts: $dto->parsedTexts(),
            conversation_id: $dto->conversation_id ?? '',
            total_price: $dto->total_price,
            message_id: $dto->message_id,
        );
    }
}
