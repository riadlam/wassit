<x-filament-panels::page>
    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold">Emotes</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Listing pages load these from the database. Use Check Emotes to hit the wiki once and save anything new.
            </p>

            <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">In database</dt>
                    <dd class="mt-1 text-2xl font-semibold">{{ $emoteDbCount }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Last wiki check</dt>
                    <dd class="mt-1 font-medium">
                        @if(!empty($emoteResult['last_checked_at']))
                            {{ \Illuminate\Support\Carbon::parse($emoteResult['last_checked_at'])->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </dd>
                </div>
            </dl>

            @if($emoteResult)
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                    Wiki had {{ $emoteResult['remote_count'] ?? 0 }} emotes.
                    New saved: {{ $emoteResult['added_count'] ?? 0 }}.
                </p>
                @if(!empty($emoteResult['added']))
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach($emoteResult['added'] as $name)
                            <li>{{ $name }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold">Recalls</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Listing pages load these from the database. Use Check Recalls to hit the wiki once and save anything new.
            </p>

            <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">In database</dt>
                    <dd class="mt-1 text-2xl font-semibold">{{ $recallDbCount }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Last wiki check</dt>
                    <dd class="mt-1 font-medium">
                        @if(!empty($recallResult['last_checked_at']))
                            {{ \Illuminate\Support\Carbon::parse($recallResult['last_checked_at'])->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </dd>
                </div>
            </dl>

            @if($recallResult)
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                    Wiki had {{ $recallResult['remote_count'] ?? 0 }} recalls.
                    New saved: {{ $recallResult['added_count'] ?? 0 }}.
                </p>
                @if(!empty($recallResult['added']))
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach($recallResult['added'] as $name)
                            <li>{{ $name }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </div>
    </div>
</x-filament-panels::page>
