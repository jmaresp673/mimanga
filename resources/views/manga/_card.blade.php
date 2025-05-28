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
    class="border rounded dark:bg-gray-800 p-4 shadow-md w-[11rem] sm:w-56 md:w-60 xl:w-[17rem] mx-auto"
>
    <!-- Enlace al show de la serie -->
    <a href="{{ route('series.show', $manga['id']) }}" class="!text-blue-500 !hover:underline">
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

        <div class="text-end">
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
        <div class="text-center mb-2">
            <x-text class="!p-0 text-xl font-bold">{{ $manga['title']['romaji'] }}</x-text>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $manga['title']['native'] }}
            </p>
        </div>
        <div class="w-full h-[1px] bg-gray-400 dark:bg-white"></div>
        <div class="flex flex-row flex-wrap justify-between items-center mb-2 px-3 text-nowrap">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Rating:') }} {{ $manga['averageScore'] }}%
                @switch(true)
                    @case($manga['averageScore'] == 0)
                        {{ __('No score') }}
                        @break
                    @case($manga['averageScore'] >= 1 && $manga['averageScore'] <= 20)
                        <i class="fa-solid fa-poo fa-sm text-red-600"></i>
                        @break
                    @case($manga['averageScore'] >= 21 && $manga['averageScore'] <= 49)
                        <i class="fa-solid fa-frown fa-sm text-orange-500"></i>
                        @break
                    @case($manga['averageScore'] >= 50 && $manga['averageScore'] <= 65)
                        <i class="fa-solid fa-face-meh fa-sm text-yellow-400"></i>
                        @break
                    @case($manga['averageScore'] >= 66 && $manga['averageScore'] <= 75)
                        <i class="fa-solid fa-smile-beam fa-sm text-emerald-400"></i>
                        @break
                    @case($manga['averageScore'] >= 76 && $manga['averageScore'] <= 85)
                        <i class="fa-solid fa-laugh-beam fa-sm text-green-600"></i>
                        @break
                    @case($manga['averageScore'] >= 86 && $manga['averageScore'] <= 95)
                        <i class="fa-solid fa-face-grin-stars fa-sm text-blue-500"></i>

                        @break
                    @case($manga['averageScore'] >= 96 && $manga['averageScore'] <= 100)
                        <i class="fa-solid fa-trophy fa-sm text-yellow-500"></i>
                        @break
                @endswitch
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $manga['status'] }}
            </p>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            @if ($manga['chapters'])
                {{ $manga['chapters'] }}
                @if($manga['chapters'] === 1)
                    {{ __('Chapter') }}
                @else
                    {{ __('Chapters') }}
                @endif
            @endif
            @if ($manga['volumes'])
                - {{ $manga['volumes'] }}
                @if($manga['volumes'] === 1)
                    {{ __('Volume') }}
                @else
                    {{ __('Volumes') }}
                @endif
            @endif
        </p>
        <p class="text-sm text-gray-500 mb-2">
            {{ $manga['startDate']['year'] ?? 'unknown' }}
            @if ($manga['endDate']['year'] && $manga['startDate']['year'] !== $manga['endDate']['year'])
                - {{ $manga['endDate']['year'] }}
            @elseif (!$manga['endDate']['year'])
                - {{ __('Ongoing') }}
            @endif
        </p>
        <p class="text-sm text-gray-600 dark:text-gray-400">
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
