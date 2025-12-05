<?php

namespace App\Pulse\Recorders;

use Carbon\CarbonImmutable;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Laravel\Pulse\Pulse;

class ScheduleRecorder
{
    /**
     * The events to listen for.
     *
     * @var list<class-string>
     */
    public array $listen = [
        ScheduledTaskStarting::class,
        ScheduledTaskFinished::class,
        ScheduledTaskFailed::class,
    ];

    public function __construct(
        protected Pulse $pulse,
    ) {
        //
    }

    public function record(ScheduledTaskStarting|ScheduledTaskFinished|ScheduledTaskFailed $event): void
    {
        $timestamp = CarbonImmutable::now()->getTimestamp();

        $taskName = $event->task->description ?? $event->task->command ?? $event->task->expression ?? 'task';

        $type = match ($event::class) {
            ScheduledTaskStarting::class => 'schedule_starting',
            ScheduledTaskFinished::class => 'schedule_finished',
            ScheduledTaskFailed::class => 'schedule_failed',
            default => 'schedule',
        };

        $this->pulse->record(
            type: $type,
            key: (string) $taskName,
            timestamp: $timestamp,
        )->count()->onlyBuckets();

        if ($event instanceof ScheduledTaskFinished) {
            $this->pulse->record(
                type: 'schedule_runtime',
                key: (string) $taskName,
                value: (int) round($event->runtime * 1000),
                timestamp: $timestamp,
            )->avg()->max();
        }
    }
}
