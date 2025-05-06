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
    class="border rounded p-4 shadow-md w-72 mx-auto"
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

        <x-text class="!p-0 mt-4 text-xl font-bold">{{ $manga['title']['romaji'] }}</x-text>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ $manga['title']['native'] }}
        </p>
        <p class="text-sm text-gray-500 mt-1">
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
