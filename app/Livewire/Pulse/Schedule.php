<?php

namespace App\Livewire\Pulse;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Livewire\Card;

class Schedule extends Card
{
    public int|string|null $cols = 4;

    public function render(): Renderable
    {
        [$counts, $time, $runAt] = $this->remember(function ($interval = null) {
            return $this->aggregateTypes([
                'schedule_starting',
                'schedule_finished',
                'schedule_failed',
            ], 'count');
        });

        [$runtimes] = $this->remember(function ($interval = null) {
            return $this->aggregate('schedule_runtime', ['avg', 'max'], orderBy: 'avg');
        }, 'runtime');

        $counts = collect($counts);
        $runtimes = collect($runtimes);

        return View::make('pulse::custom.schedule', [
            'time' => $time,
            'runAt' => $runAt,
            'counts' => $counts,
            'runtimes' => $runtimes,
        ]);
    }
}
