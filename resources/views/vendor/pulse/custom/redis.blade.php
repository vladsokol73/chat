<x-pulse::card :cols="$cols ?? null" :rows="$rows ?? null" :expand="$expand ?? false" :class="$class ?? ''">
    <x-pulse::card-header name="Redis" details="past {{ $this->periodForHumans() }}" />

    <div class="divide-y divide-zinc-950/[.06] dark:divide-white/5">
        @php($rows = $rows instanceof \Illuminate\Support\Collection ? $rows : collect($rows))
        @forelse($rows as $row)
            <div class="flex items-center justify-between py-2 text-sm">
                <div class="font-mono text-zinc-600 dark:text-zinc-400">{{ $row->key }}</div>
                <div class="font-semibold">{{ $row->count }}</div>
            </div>
        @empty
            <x-pulse::no-results />
        @endforelse
    </div>
</x-pulse::card>


