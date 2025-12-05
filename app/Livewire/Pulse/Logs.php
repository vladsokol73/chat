<?php

namespace App\Livewire\Pulse;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Livewire\Card;

class Logs extends Card
{
    public int|string|null $cols = 4;

    public array $levelFilter = [];

    public string $search = '';

    public int $messageLimit = 100;

    public bool $newestFirst = true;

    public function render(): Renderable
    {
        [$levels, $time, $runAt] = $this->remember(function ($interval = null) {
            $rows = $this->aggregate('log', 'count', orderBy: 'count', limit: 1000)
                ->map(fn ($row) => (object) [
                    'level' => (string) $row->key,
                    'count' => (int) $row->count,
                ]);

            return $rows;
        });

        $levels = collect($levels);

        [$messages] = $this->remember(function ($interval = null) {
            return $this->values('log_message')->sortByDesc('timestamp')->take(500)->map(function ($row) {
                $data = json_decode($row->value ?? '{}', true) ?: [];

                return (object) [
                    'timestamp' => (int) $row->timestamp,
                    'level' => (string) ($data['level'] ?? ''),
                    'message' => (string) ($data['message'] ?? ''),
                    'context' => (string) ($data['context'] ?? ''),
                ];
            });
        }, 'messages');
        $messages = collect($messages);

        // Apply filters
        if (! empty($this->levelFilter)) {
            $selected = array_map(fn ($v) => strtoupper((string) $v), $this->levelFilter);
            $messages = $messages->filter(function ($m) use ($selected) {
                return in_array(strtoupper($m->level), $selected, true);
            });
        }

        if ($this->search !== '') {
            $q = mb_strtolower($this->search);
            $messages = $messages->filter(function ($m) use ($q) {
                return mb_stripos($m->message, $q) !== false || mb_stripos($m->context, $q) !== false;
            });
        }

        // Order
        $messages = $this->newestFirst
            ? $messages->sortByDesc('timestamp')
            : $messages->sortBy('timestamp');

        $messages = $messages->take(max(10, min(500, $this->messageLimit)));

        return View::make('pulse::custom.logs', [
            'time' => $time,
            'runAt' => $runAt,
            'levels' => $levels,
            'messages' => $messages,
            'levelFilter' => $this->levelFilter,
            'search' => $this->search,
            'messageLimit' => $this->messageLimit,
            'newestFirst' => $this->newestFirst,
        ]);
    }
}
