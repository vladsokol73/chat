<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

readonly class TranslatorService
{
    /**
     * Translate text using external translator API
     *
     * @throws RequestException
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function translate(string $lang, string $text): string
    {
        $apiUrl = config('app.translator-url');
        $apiKey = config('app.translator-key');

        if (! $apiUrl || ! $apiKey) {
            throw new RuntimeException('Translator API configuration is missing');
        }

        $response = Http::withHeaders([
            'Authorization' => $apiKey,
        ])
            ->timeout(30)
            ->acceptJson()
            ->get($apiUrl, [
                'lang' => $lang,
                'text' => $text,
            ])
            ->throw();

        $data = $response->json();

        if (! isset($data['translation']) || ! is_string($data['translation'])) {
            throw new RuntimeException('Invalid response format from translator API');
        }

        return $data['translation'];
    }
}
