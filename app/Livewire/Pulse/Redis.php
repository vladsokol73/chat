<?php

namespace App\Livewire\Pulse;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Livewire\Card;

class Redis extends Card
{
    public int|string|null $cols = 4;

    public function render(): Renderable
    {
        [$rows, $time, $runAt] = $this->remember(function ($interval = null) {
            return $this->aggregate('redis_command', 'count', orderBy: 'count')
                ->map(fn ($row) => (object) [
                    'key' => (string) $row->key,
                    'count' => (int) $row->count,
                ]);
        });

        $rows = collect($rows);

        return View::make('pulse::custom.redis', [
            'time' => $time,
            'runAt' => $runAt,
            'rows' => $rows,
        ]);
    }
}
