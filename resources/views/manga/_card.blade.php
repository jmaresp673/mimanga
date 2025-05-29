@php
    $delay = ($loop->index ?? 0) * 100;
@endphp

<div
    x-data="{ show: false, loaded: false }"
    x-init="
        setTimeout(() => show = true, {{ $delay }});
        // Verificar si la imagen ya está cargada desde caché
        $nextTick(() => {
            if ($refs.img?.complete) loaded = true;
        });
    "
    x-show="show"
    x-transition:enter="transition ease-out duration-1000 transform"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    class="border rounded dark:bg-gray-800 p-4 shadow-md w-[11rem] sm:w-56 md:w-60 xl:w-[17rem] mx-auto hover:scale-105 transition-all duration-300 hover:shadow-[0_2px_12px_0_rgba(99,102,241,0.35)]"
>
    <!-- Enlace al show de la serie -->
    <a href="{{ route('series.show', ['anilistId' => $manga['id'], 'slug' => \Illuminate\Support\Str::slug($manga['title']['romaji'])]) }}"
       class="!text-blue-500 !hover:underline">
        <!-- Placeholder mientras la imagen carga -->
        <div x-show="!loaded" x-cloak class="w-full h-80 bg-gray-300 animate-pulse rounded"></div>

        <!-- Imagen con fade-in al cargar -->
        <img
            x-ref="img"
            loading="lazy"
            @load="loaded = true"
            :class="{ 'opacity-0': !loaded, 'opacity-100': loaded }"
            class="transition-opacity duration-700 w-80 object-cover rounded "
            src="{{ $manga['coverImage']['large'] }}"
            alt="{{ $manga['title']['romaji'] }}"
        >

        <div class="flex flex-row justify-between">
            <p class="text-xs
            @switch($manga['status'])
                @case('FINISHED') text-green-600 @break
                @case('RELEASING') text-blue-500 @break
                @case('NOT_YET_RELEASED') text-yellow-500 @break
                @case('CANCELLED') text-red-600 @break
                @case('HIATUS') text-orange-500 @break
                @default text-gray-600 @break
             @endswitch">
                @switch($manga['status'])
                    @case('FINISHED')
                        {{ __('FINISHED') }}
                        @break
                    @case('RELEASING')
                        {{ __('RELEASING') }}
                        @break
                    @case('NOT_YET_RELEASED')
                        {{ __('NOT YET RELEASED') }}
                        @break
                    @case('CANCELLED')
                        {{ __('CANCELLED') }}
                        @break
                    @case('HIATUS')
                        {{ __('HIATUS') }}
                        @break
                    @default
                        {{ __('UNKNOWN') }}
                        @break
                @endswitch
            </p>
            <p class="text-xs text-gray-600 dark:text-gray-400">
                @switch($manga['format'])
                    @case('MANGA')
                        {{ __('MANGA') }}
                        @break
                    @case('NOVEL')
                        {{ __('NOVEL') }}
                        @break
                    @case('ONE_SHOT')
                        {{ __('ONE SHOT') }}
                        @break
                @endswitch
            </p>
        </div>
        <div class="text-center">
            <x-text
                class="line-clamp-2 sm:line-clamp-3 md:line-clamp-none !p-0 text-base sm:text-xl font-bold">{{ $manga['title']['romaji'] }}</x-text>
            <p class="line-clamp-1 text-xs text-gray-600 dark:text-gray-400">
                {{ $manga['title']['native'] }}
            </p>
        </div>
        <div class="w-full my-1 sm:mb-2 h-[1px] bg-gray-400 dark:bg-white"></div>
        <div class="flex flex-row flex-wrap justify-between items-center mb-2 px-3 text-nowrap">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Rating:') }} {{ $manga['averageScore'] }}%
                @switch(true)
                    @case($manga['averageScore'] == 0)
                        {{ __('No score') }}
                        @break
                    @case($manga['averageScore'] >= 1 && $manga['averageScore'] <= 20)
                        <i class="ml-1 fa-solid fa-poo fa-sm text-red-600"></i>
                        @break
                    @case($manga['averageScore'] >= 21 && $manga['averageScore'] <= 49)
                        <i class="ml-1 fa-solid fa-frown fa-sm text-orange-500"></i>
                        @break
                    @case($manga['averageScore'] >= 50 && $manga['averageScore'] <= 65)
                        <i class="ml-1 fa-solid fa-face-meh fa-sm text-yellow-400"></i>
                        @break
                    @case($manga['averageScore'] >= 66 && $manga['averageScore'] <= 75)
                        <i class="ml-1 fa-solid fa-smile-beam fa-sm text-emerald-400"></i>
                        @break
                    @case($manga['averageScore'] >= 76 && $manga['averageScore'] <= 85)
                        <i class="ml-1 fa-solid fa-laugh-beam fa-sm text-blue-500"></i>
                        @break
                    @case($manga['averageScore'] >= 86)
                        <i class="ml-1 fa-solid fa-trophy fa-sm text-yellow-500"></i>
                        @break
                @endswitch
            </p>
        </div>
        <div class="flex flex-col sm:flex-row">
{{--            <p class="text-sm text-gray-600 dark:text-gray-400">--}}
{{--                @if ($manga['chapters'])--}}
{{--                    {{ $manga['chapters'] }}--}}
{{--                    @if($manga['chapters'] === 1)--}}
{{--                        {{ __('Chapter') }}--}}
{{--                    @else--}}
{{--                        {{ __('Chapters') }}--}}
{{--                    @endif--}}
{{--                @endif--}}
{{--            </p>--}}
            <p class="text-sm text-gray-600 dark:text-gray-400">
                @if ($manga['volumes'])
{{--                    <span class="hidden sm:inline">&nbsp;-</span>--}}
                    {{ $manga['volumes'] }}
                    @if($manga['volumes'] === 1)
                        {{ __('Volume') }}
                    @else
                        {{ __('Volumes') }}
                    @endif
                @endif
            </p>
        </div>
        <p class="text-sm text-gray-500 mb-2">
            {{ $manga['startDate']['year'] ?? 'unknown' }}
            @if ($manga['endDate']['year'] && $manga['startDate']['year'] !== $manga['endDate']['year'])
                - {{ $manga['endDate']['year'] }}
            @elseif (!$manga['endDate']['year'])
                - {{ __('Ongoing') }}
            @endif
        </p>
        <p class="line-clamp-1 sm:line-clamp-4 text-sm text-gray-600 dark:text-gray-400">
            @foreach ($manga['main_authors'] as $author)
                <a href="{{ route('authors.show', $author['id']) }}" class="!text-blue-500 !hover:underline">
                    @if ($author['type'] === 'story & art')
                        {{ $author['name'] }}
                    @else
                        {{ ucwords($author['type']) }}: {{ $author['name'] }}
                    @endif
                </a><br>
            @endforeach
        </p>
    </a>
</div>
