<x-app-layout>
    <div class="pt-6 sm:pt-0 pb-10">
        <x-main-container>
            {{-- Top: imagen / título y autor --}}
            <div class="flex flex-col sm:flex-row items-center gap-6 mb-2 sm:mb-8">
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
                    <x-text
                        class="flex flex-row flex-wrap justify-center sm:justify-start items-center gap-2 mt-1 !p-0">
                        @foreach($mainAuthors as $a)
                            <span class="flex flex-col sm:flex-row flex-wrap justify-center items-center">
                            <div>{{ ucfirst($a['role']) }}:&nbsp;</div>
                            <a href="{{ $a['id']
                                ? route('authors.show', $a['id'])
                                : '#' }}"
                               class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $a['name'] }}
                            </a>
                            </span>
                            @if(! $loop->last)
                                <span>&middot;</span>
                            @endif
                        @endforeach
                    </x-text>

                    @php
                        // Limite de palabras para mostrar antes del collapse
                        $wordLimit = 20;
                        // Dividir la descripción en dos partes
                        $descriptionParts = explode(' ', $media['description'], $wordLimit + 1);
                        $showCollapse = count($descriptionParts) > $wordLimit;
                        $firstPart = implode(' ', array_slice($descriptionParts, 0, $wordLimit));
                        $secondPart = implode(' ', array_slice($descriptionParts, $wordLimit));
                        // Si la descripción es muy corta o no hay, no mostrar el collapse
                        if (strlen($firstPart) < 50 || empty($media['description'])) {
                            $showCollapse = false;
                        } else $firstPart .= '...';
                    @endphp

                    @if($showCollapse)
                        <div tabindex="0" class="visible sm:hidden collapse collapse-plus">
                            <x-text class="mx-3 sm:mx-0 collapse-title font-semibold !pb-0 w-auto text-wrap">
                                {!! htmlspecialchars_decode($firstPart) !!}
                            </x-text>
                            <x-text class="mx-3 sm:mx-0 collapse-content text-sm !pt-1">
                                {!! htmlspecialchars_decode($secondPart) !!}
                            </x-text>
                        </div>
                    @else
                        <x-text class="block sm:hidden mx-3 sm:mx-0">
                            {!! $media['description'] !!}
                        </x-text>
                    @endif
                    <x-text class="hidden sm:block mx-3 mt-3 sm:mx-0">
                        {!! $media['description'] !!}
                    </x-text>
                </div>
            </div>

            {{-- Cuerpo: sidebar / ediciones --}}
            <div class="grid grid-cols-4 lg:grid-cols-4 gap-6">
                {{-- Sidebar info --}}
                <aside class="space-y-4 col-span-1 bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <p><strong>{{__('Format: ')}}</strong> {{ $media['format'] }}</p>
                    <p><strong>{{__('Status: ')}}</strong> {{ ucfirst(strtolower($media['status'])) }}</p>
                    @if($media['volumes'])
                        <p><strong>{{__('Volumes')}}</strong> {{ $media['volumes'] }}</p>
                    @endif
                    @if($media['chapters'])
                        <p><strong>{{__('Chapters')}}</strong> {{ $media['chapters'] }}</p>
                    @endif
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

                {{-- Ediciones --}}
                <section class="col-span-3 bg-white dark:bg-gray-800 p-4 rounded shadow relative overflow-hidden"
                         style="background-image: url('{{ $media['bannerImage'] }}'); background-size: cover; background-position: center;">
                    <!-- Capa oscura (solo fondo) -->
                    <div class="absolute inset-0 bg-black opacity-40 z-0"></div>

                    <!-- Contenido (debe estar en una capa superior) -->
                    <div class="relative z-10 space-y-4"> <!-- Añade posición relativa y z-index mayor -->
                        <h3 class="text-xl font-semibold">{{__('Editions for this series')}}</h3>

                        @if(count($editions))
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                <x-edition-card
                                    :id="$media['id'].'ES'"
                                    :cover="$editions[0]['portada']"
                                    :title="$spanishTitle"
                                    lang="ES"
                                    :count="$general['numbers_localized']"
                                    :edition="$general"
                                    :volumes="$editions"
                                />
                            </div>
                        @else
                            <p class="text-center text-gray-500">No hay ediciones disponibles.</p>
                        @endif
                    </div>
                </section>


            </div>
        </x-main-container>
    </div>
</x-app-layout>
