<x-app-layout>
    {{--    <x-slot name="header">--}}
    {{--        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">--}}
    {{--            {{ $media['title']['romaji'] }}--}}
    {{--        </h1>--}}
    {{--    </x-slot>--}}

    <div class="py-6">
        <x-main-container>
            {{-- Top: imagen / título y autor --}}
            <div class="flex flex-col sm:flex-row items-center gap-6 mb-8">
                <img src="{{ $media['coverImage']['large'] }}"
                     alt="{{ $media['title']['romaji'] }}"
                     class="w-48 h-auto rounded shadow-lg flex-shrink-0">

                <div class="text-center md:text-left">
                    <h2 class="text-3xl font-extrabold text-gray-800 dark:text-gray-100">
                        {{ $media['title']['romaji'] }}
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300">
                        {{ $media['title']['native'] }}
                    </p>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-2">
                        {{ $media['title']['english'] }}
                    </p>
                    <div class="flex flex-col">
                        <p class="text-gray-700 dark:text-gray-400">
                            <strong>{{ __('Main author: ') }}</strong>
                        </p>
                        <x-text class="flex flex-row gap-2 mt-1 !p-0">
                            @foreach($mainAuthors as $a)
                                    {{ ucfirst($a['role']) }}:
                                    <a href="{{ $a['id']
                                ? route('authors.show', $a['id'])
                                : '#' }}"
                                       class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $a['name'] }}
                                    </a>
                                    @if(! $loop->last) &middot; @endif
                            @endforeach
                        </x-text>
                    </div>
                </div>
            </div>

            {{-- Cuerpo: sidebar / ediciones --}}
            <div class="grid grid-cols-4 lg:grid-cols-4 gap-6">
                {{-- Sidebar info --}}
                <aside class="space-y-4 col-span-1 bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <p><strong>Formato:</strong> {{ $media['format'] }}</p>
                    <p><strong>Estado:</strong> {{ ucfirst(strtolower($media['status'])) }}</p>
                    <p><strong>Volúmenes:</strong> {{ $media['volumes'] }}</p>
                    <p><strong>Capítulos:</strong> {{ $media['chapters'] }}</p>
                    @if(!empty($media['genres']))
                        <p><strong>Géneros:</strong><br>
                            <span class="flex flex-wrap gap-2 mt-1">
                                @foreach($media['genres'] as $genre)
                                    <span class="text-sm bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">
                                        {{ $genre }}
                                    </span>
                                @endforeach
                            </span>
                        </p>
                    @endif
                </aside>

                {{-- Ediciones (vacío por ahora) --}}
                <section class="col-span-3 bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <h3 class="text-xl font-semibold mb-4">Ediciones</h3>
                    <p class="text-gray-500 dark:text-gray-400">Aquí irán las ediciones disponibles.</p>
                </section>
            </div>
        </x-main-container>
    </div>
</x-app-layout>
