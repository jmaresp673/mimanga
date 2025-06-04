<x-app-layout>
    <div class="pt-6 sm:pt-0 pb-10">
        <x-main-container>
            {{-- Top: imagen / título y autor --}}
            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-6 mb-2 sm:mb-8">
                <div class="relative w-48 h-auto rounded flex-shrink-0">
                    {{-- Imagen con click modal --}}
                    <img src="{{ $media['coverImage']['large'] }}"
                         alt="{{ $media['title']['romaji'] }}"
                         class="cursor-pointer w-48 h-auto rounded shadow-lg flex-shrink-0"
                         onclick="my_modal_2.showModal()">
                    <i class="fa-solid fa-magnifying-glass absolute top-3 right-3 text-md rounded-full p-2 text-indigo-500  bg-gray-800/80 dark:bg-white/80 pointer-events-none transition-all duration-200"></i>
                    {{-- Ventana modal con imagen --}}
                    <dialog id="my_modal_2" class="modal">
                        <div class="modal-box bg-transparent">
                            <img src="{{ $media['coverImage']['large'] }}"
                                 alt="{{ $media['title']['romaji'] }}"
                                 class="w-full h-auto rounded shadow-lg flex-shrink-0">
                        </div>
                        <form method="dialog" class="modal-backdrop">
                            <button></button>
                        </form>
                    </dialog>
                    <div class="flex flex-row">
                        @if($media['startDate']['year'])
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $media['startDate']['year'] }}
                            </p>
                        @endif
                        @if($media['endDate']['year'])
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                &nbsp;- {{ $media['endDate']['year'] }}
                            </p>
                        @else
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                &nbsp;- {{ __('Ongoing') }}
                            </p>
                        @endif
                    </div>
                </div>

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
                            <div>
                                @switch($a['role'])
                                    @case('story & art')
                                    {{ __('Story & Art') }}@break
                                    @case('story')
                                    {{ __('Story') }}@break
                                    @case('art')
                                    {{ __('Art') }}@break
                                    @case('original creator')
                                    {{ __('Original Creator') }}@break
                                    @case('illustration')
                                    {{ __('Illustration') }}@break
                                @endswitch:&nbsp;
                            </div>
                            <a href="{{ $a['id']
                                ? route('authors.show', $a['id'])
                                : '#' }}"
                               class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{  ucwords($a['name']) }}
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
                        <div class="visible sm:hidden w-full px-3 py-3">
                            <div tabindex="0"
                                 class="visible sm:hidden collapse collapse-plus border-l-4 border-gray-800 dark:border-gray-300 rounded-l-xl px-3 py-2">
                                <x-text class="!p-0 text-left mx-0 collapse-title font-semibold !pb-0 w-auto text-wrap">
                                    {!! htmlspecialchars_decode($firstPart) !!}
                                </x-text>
                                <x-text class="!p-0 text-left mx-0 collapse-content text-sm !pt-1 text-wrap">
                                    {!! htmlspecialchars_decode($secondPart) !!}
                                </x-text>
                            </div>
                        </div>
                    @else
                        <x-text
                            class="block text-left sm:hidden border-l-4 border-gray-800 dark:border-gray-300 rounded-l-xl !py-2 mx-3 sm:mx-0">
                            {!! $media['description'] !!}
                        </x-text>
                    @endif
                    <x-text
                        class="hidden text-left border-l-4 border-gray-800 dark:border-gray-300 rounded-l-xl !py-2 sm:block mx-3 mt-3 sm:mx-0">
                        {!! $media['description'] !!}
                    </x-text>
                </div>
            </div>

            {{-- Cuerpo: sidebar / ediciones --}}
            <div class="grid grid-cols-1 sm:grid-cols-4 lg:grid-cols-4 gap-6">
                {{-- Sidebar info --}}
                <aside
                    class="text-gray-900 dark:text-gray-100 space-y-4 col-span-3 sm:col-span-1 bg-white dark:bg-gray-800 p-4 sm:rounded-md shadow w-full">
                    <p><strong>{{__('Format: ')}}</strong>
                        @switch($media['format'])
                            @case('MANGA')
                                {{ __('Manga') }}
                                @break
                            @case('NOVEL')
                                {{ __('Novel') }}
                                @break
                            @case('ONE_SHOT')
                                {{ __('One Shot') }}
                                @break
                        @endswitch</p>
                    <p><strong>{{__('Status: ')}}</strong>
                        <span class="@switch($media['status'])
                            @case('FINISHED') text-green-600 @break
                            @case('RELEASING') text-blue-500 @break
                            @case('NOT_YET_RELEASED') text-yellow-500 @break
                            @case('CANCELLED') text-red-600 @break
                            @case('HIATUS') text-orange-500 @break
                            @default text-gray-600 @break
                        @endswitch">
                        @switch($media['status'])
                                @case('FINISHED')
                                    {{ __('Finished') }}
                                    @break
                                @case('RELEASING')
                                    {{ __('Releasing') }}
                                    @break
                                @case('NOT_YET_RELEASED')
                                    {{ __('Not yet released') }}
                                    @break
                                @case('CANCELLED')
                                    {{ __('Cancelled') }}
                                    @break
                                @case('HIATUS')
                                    {{ __('Hiatus') }}
                                    @break
                                @default
                                    {{ __('Unknown') }}
                                    @break
                            @endswitch
                    </span>
                    </p>
                    @if($media['volumes'])
                        <p><strong>{{__('Volumes')}}:</strong> {{ $media['volumes'] }}</p>
                    @endif
                    @if($media['chapters'])
                        <p><strong>{{__('Chapters')}}:</strong> {{ $media['chapters'] }}</p>
                    @endif
                    @if(!empty($media['genres']))
                        <p><strong>{{__('Genres')}}:</strong><br>
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
                <section class="col-span-3 bg-white dark:bg-gray-800 p-4 sm:rounded-md shadow relative overflow-hidden"
                         style="background-image: url('{{ $media['bannerImage'] }}'); background-size: cover; background-position: center;">
                    <!-- Capa oscura (fondo) -->
                    <div class="absolute inset-0 bg-black opacity-40 z-0"></div>

                    <!-- Contenido -->
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
                                    :publisher="$general['localized_publisher']['name']"
                                />
                            </div>
                        @else
                            <p class="text-center text-gray-400 bg-gray-700/70 w-fit px-3 py-1 m-auto rounded-lg">No hay
                                ediciones disponibles.</p>
                        @endif
                    </div>
                </section>
            </div>
        </x-main-container>
    </div>
</x-app-layout>
