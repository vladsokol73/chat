<?php

namespace App\Pulse\Recorders;

use Carbon\CarbonImmutable;
use Illuminate\Redis\Events\CommandExecuted;
use Laravel\Pulse\Pulse;

class RedisRecorder
{
    /**
     * The events to listen for.
     *
     * @var list<class-string>
     */
    public array $listen = [
        CommandExecuted::class,
    ];

    public function __construct(
        protected Pulse $pulse,
    ) {
        //
    }

    public function record(CommandExecuted $event): void
    {
        $timestamp = CarbonImmutable::now()->getTimestamp();

        $command = strtolower($event->command);
        $connection = (string) $event->connectionName;
        $key = $connection.':'.$command;

        $this->pulse->record(
            type: 'redis_command',
            key: $key,
            timestamp: $timestamp,
            value: is_numeric($event->time) ? (int) round($event->time) : null,
        )->count();

        if (is_numeric($event->time)) {
            $this->pulse->record(
                type: 'redis_command_time',
                key: $key,
                timestamp: $timestamp,
                value: (int) round($event->time),
            )->avg()->max();
        }
    }
}
