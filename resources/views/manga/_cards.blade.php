@forelse($results as $manga)
    @include('manga._card', ['manga' => $manga])
@empty
    <x-text x-data="{ show: false }"
            x-init="setTimeout(() => show = true, 10)"
            x-show="show"
            x-transition:enter="transition-opacity duration-1500"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            id="no-more-results" class="text-center col-span-full">{{ __('No further results found') }}
    </x-text>
@endforelse
