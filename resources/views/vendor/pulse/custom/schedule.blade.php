<x-pulse::card :cols="$cols ?? null" :rows="$rows ?? null" :expand="$expand ?? false" :class="$class ?? ''">
    <x-pulse::card-header name="Schedule" details="past {{ $this->periodForHumans() }}" />

    <div class="space-y-4">
        <div>
            <div class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Counts</div>
            <div class="mt-2 grid grid-cols-3 gap-2 text-sm">
                @php($counts = $counts instanceof \Illuminate\Support\Collection ? $counts : collect($counts))
                @foreach($counts as $type => $items)
                    <div>
                        <div class="font-medium">{{ str_replace('_', ' ', $type) }}</div>
                        <div class="text-lg font-semibold">{{ $items->sum('count') }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <div class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Runtime (ms)</div>
            <div class="mt-2 space-y-1 text-sm">
                @php($runtimes = $runtimes instanceof \Illuminate\Support\Collection ? $runtimes : collect($runtimes))
                @forelse($runtimes as $row)
                    <div class="flex items-center justify-between">
                        <div class="font-mono text-zinc-600 dark:text-zinc-400">{{ $row->key }}</div>
                        <div>avg {{ (int) $row->avg }} · max {{ (int) $row->max }}</div>
                    </div>
                @empty
                    <x-pulse::no-results />
                @endforelse
            </div>
        </div>
    </div>
</x-pulse::card>


