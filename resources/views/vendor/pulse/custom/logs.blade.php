<x-pulse::card :cols="$cols ?? null" :rows="$rows ?? null" :expand="$expand ?? false" :class="$class ?? ''">
    <x-pulse::card-header name="Logs" details="past {{ $this->periodForHumans() }}">
        <div class="flex items-center gap-2 flex-wrap">
            <input
                wire:model.debounce.400ms="search"
                type="text"
                placeholder="Search message/context..."
                class="px-2 py-1 text-xs rounded border border-zinc-300 dark:border-zinc-700 bg-white/70 dark:bg-zinc-900/70"
            />
            <label class="flex items-center gap-1 text-xs text-zinc-600 dark:text-zinc-300">
                <input type="checkbox" wire:model="newestFirst" class="rounded border-zinc-300 dark:border-zinc-700">
                Newest first
            </label>
            <select wire:model="messageLimit" class="px-2 py-1 text-xs rounded border border-zinc-300 dark:border-zinc-700 bg-white/70 dark:bg-zinc-900/70">
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="200">200</option>
                <option value="500">500</option>
            </select>
            <div class="flex items-center gap-1 text-xs">
                @php($available = ['debug','info','notice','warning','error','critical','alert','emergency'])
                @foreach($available as $lvl)
                    <label class="inline-flex items-center gap-1 px-1 py-[2px] rounded border border-zinc-300 dark:border-zinc-700">
                        <input type="checkbox" value="{{ $lvl }}" wire:model="levelFilter" class="rounded border-zinc-300 dark:border-zinc-700">
                        {{ strtoupper($lvl) }}
                    </label>
                @endforeach
            </div>
        </div>
    </x-pulse::card-header>

    <div class="divide-y divide-zinc-950/[.06] dark:divide-white/5">
        @php($levels = $levels instanceof \Illuminate\Support\Collection ? $levels : collect($levels))
        @forelse($levels as $row)
            <div class="flex items-center justify-between py-2 text-sm">
                <div class="font-mono text-zinc-600 dark:text-zinc-400">{{ strtoupper($row->level) }}</div>
                <div class="font-semibold">{{ $row->count }}</div>
            </div>
        @empty
            <x-pulse::no-results />
        @endforelse
    </div>

    <div class="mt-4">
        <div class="text-xs uppercase text-zinc-500 dark:text-zinc-400 mb-2">Recent logs</div>
        @php($messages = $messages instanceof \Illuminate\Support\Collection ? $messages : collect($messages))
        <div class="space-y-2 max-h-96 overflow-y-auto">
            @forelse($messages as $m)
                <div class="text-xs">
                    <div class="flex justify-between">
                        <code class="font-mono text-zinc-600 dark:text-zinc-400">{{ strtoupper($m->level) }}</code>
                        <span class="text-zinc-500 dark:text-zinc-400">{{ \Carbon\CarbonImmutable::createFromTimestamp($m->timestamp)->toDateTimeString() }}</span>
                    </div>
                    <div class="text-zinc-900 dark:text-zinc-100">{{ $m->message }}</div>
                    @if($m->context)
                        <pre class="text-[10px] text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $m->context }}</pre>
                    @endif
                </div>
            @empty
                <x-pulse::no-results />
            @endforelse
        </div>
    </div>
</x-pulse::card>

