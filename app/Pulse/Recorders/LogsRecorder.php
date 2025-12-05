<?php

namespace App\Pulse\Recorders;

use Carbon\CarbonImmutable;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Str;
use Laravel\Pulse\Pulse;

class LogsRecorder
{
    /**
     * The events to listen for.
     *
     * @var list<class-string>
     */
    public array $listen = [
        MessageLogged::class,
    ];

    public function __construct(
        protected Pulse $pulse,
    ) {
        //
    }

    public function record(MessageLogged $event): void
    {
        $timestamp = CarbonImmutable::now()->getTimestamp();
        $level = strtolower($event->level);

        $this->pulse->record(
            type: 'log',
            key: $level,
            timestamp: $timestamp,
        )->count()->onlyBuckets();

        // Store a sample of recent log messages as values for display
        $key = $level.':'.$timestamp.':'.Str::random(8);

        $payload = json_encode([
            'level' => $level,
            'message' => (string) $event->message,
            'context' => $this->stringifyContext($event->context ?? []),
        ], JSON_UNESCAPED_UNICODE);

        $this->pulse->set(
            type: 'log_message',
            key: $key,
            value: $payload,
            timestamp: $timestamp,
        );
    }

    protected function stringifyContext(array $context): string
    {
        $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $json !== false ? $json : '';
    }
}
