@php
    use Carbon\Carbon;
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center">
            <div class="flex-1">
                <h2 class="font-semibold text-gray-950 dark:text-white">
                    {{ Carbon::now()->toFormattedDateString() }}
                </h2>

                <p class="text-sm">
                    <small class="text-gray-500 dark:text-gray-400">
                        <span x-data="{ time: new Date().toLocaleTimeString() }" x-init="
                            setInterval(() => {
                                time = new Date().toLocaleTimeString(); // Update elke seconde
                            }, 1000);">
                            <span x-text="time"></span>
                        </span>
                    </small>
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
