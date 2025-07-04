@props([
    'volume' => \App\Models\Volume::class,
    'edition' => \App\Models\Edition::class
])

<div x-data="{
    openModal: false,
    selectedVolume: null,
    isHovered: false,
    isMobile: window.innerWidth < 640
}"
     x-init="() => {
         window.addEventListener('resize', () => {
             isMobile = window.innerWidth < 640;
         });
     }"
     class="cursor-pointer relative px-2 rounded shadow-md bg-white dark:bg-gray-800 flex flex-col items-center text-center w-32 h-48
            overflow-hidden group transition-all duration-300 ease-in-out"
     style="background-image: url('{{ $volume->cover_image_url }}'); background-size: cover; background-position: center;"
     x-on:click="if(!openModal) {
         openModal = true;
         isHovered = false;
     }"
     x-on:mouseenter="if(!openModal) isHovered = true"
     x-on:mouseleave="isHovered = false"
     x-cloak>

    <!-- Capa de información hover -->
    <div class="absolute bottom-0 w-full bg-gray-950 bg-opacity-60
                transition-all duration-300 ease-in-out flex items-center
                min-h-[4rem]"
         :class="isHovered && !openModal ? 'min-h-full' : ''">

        <div class="w-full p-2 flex flex-col justify-center h-full space-y-1">
            <h3 class="text-sm font-semibold text-gray-100 px-1">
                {{$edition->localized_title}} Nº{{ $volume->volume_number }}
            </h3>

            <div class="opacity-0 max-h-0
                       transition-all duration-300 overflow-hidden
                       flex flex-col items-center justify-center text-center"
                 :class="isHovered && !openModal ? 'opacity-100 max-h-96' : ''">
                @if($volume->total_pages)
                    <span class="text-xs text-gray-300">
                    {{ $volume->total_pages }} {{__('pages')}}
                </span>
                    <p class="text-xs text-gray-300">
                        {{ $volume->price }} €
                    </p>
                    <p class="text-xs text-gray-300">
                        {{ $volume->release_date->format('d/m/Y') }}
                    </p>
                @elseif($edition->language === "ES")
                    <p class="text-xs text-gray-300">
                        {{__('Not available yet')}}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de detalle -->
    <template x-teleport="body">
        <div x-show="openModal"
             x-transition.opacity.duration.300ms
             class="fixed inset-0 bg-black bg-opacity-70 z-50 flex items-end sm:items-center justify-center"
             x-on:click.self="openModal = false; isHovered = false"
             x-cloak
             style="backdrop-filter: blur(2px)">

            <div x-show="openModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="pb-16 sm:pb-0 text-gray-900 bg-white dark:text-white dark:bg-gray-800 rounded-t-xl sm:rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transform transition-all"
                 @click.stop
                 x-cloak>

                <!-- Cabecera -->
                <div
                    class="p-4 border-b  flex justify-between items-center border-gray-500 bg-indigo-900 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-xl font-bold text-white">
                            <span>{{ $volume->volume_number }}</span>
                            -
                            <span>{{$edition->localized_title}}</span>
                        </h3>
                        <p class="text-sm text-gray-200 mt-1">
                            {{__('Edition')}}: {{$edition->publisher->name}}
                        </p>
                    </div>
                    <x-hover-text position="left">
                        <x-slot name="trigger">
                            <button x-on:click="openModal = false; isHovered = false"
                                    class="relative flex justify-center items-center rounded-full h-8 w-8 bg-red-600 text-white hover:text-red-600 hover:bg-white text-2xl transition-colors duration-300">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </x-slot>
                        {{ __('Close') }}
                    </x-hover-text>
                </div>

                <!-- Contenido -->
                <div class="p-6 bg-white dark:bg-gray-800">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
                        <!-- Portada -->
                        <div class="col-span-2 sm:col-span-1 flex flex-col justify-center items-center gap-2 w-full">
                            {{-- Imagen con click modal --}}
                            <img src='{{ $volume->cover_image_url }}'
                                 class="cursor-pointer w-40 h-auto rounded-lg shadow-xl mx-auto"
                                 alt="Portada del volumen"
                                 onclick="document.getElementById('my_modal_{{$volume->volume_number}}').showModal()">
                            <dialog id="my_modal_{{$volume->volume_number}}" class="modal">
                                <div class="modal-box bg-transparent">
                                    <img src="{{ $volume->cover_image_url }}"
                                         alt="Portada del volumen"
                                         class="w-full h-auto rounded shadow-lg flex-shrink-0">
                                </div>
                                <form method="dialog" class="modal-backdrop">
                                    <button></button>
                                </form>
                            </dialog>
                            <div class="text-center">
                                @if($volume->price == 0 && $edition->language === "ES")
                                    <p class="text-lg font-semibold">{{__('PVP: ')}} <span
                                            class="text-sm font-semibold text-gray-400">TBA</span></p>
                                @elseif($edition->language === "ES")
                                    <p class="text-lg font-semibold">{{__('PVP: ')}} {{ $volume->price }} €</p>
                                @endif
                            </div>
                        </div>

                        <!-- Detalles -->
                        <div class="col-span-2 space-y-4 text-center sm:text-left">
                            <div class="grid grid-cols-2 gap-4">
                                @if($edition->language === "EN")
                                @else
                                    @if($volume->total_pages)
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-400 mb-1">{{__('Release Date')}}</h4>
                                            <p> {{ $volume->release_date->format('d/m/Y') }}</p>
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-400 mb-1">{{__('Pages')}}</h4>
                                            <p> {{ __('Total pages: ') }}{{ $volume->total_pages }}</p>
                                        </div>
                                    @else
                                        <div class="col-span-2 text-center sm:text-left">
                                            <h4 class="text-lg font-semibold text-gray-400 mb-1">{{__('Not available yet')}}</h4>
                                        </div>
                                    @endif
                                    @if($volume->isbn)
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-400 mb-1">{{__('Pages')}}</h4>
                                            <p> {{ __('ISBN: ') }}{{ $volume->isbn }}</p>
                                        </div>
                                    @endif
                                @endif

                            </div>

                            {{-- Botones de accion --}}
                            <div x-data="volumeActions({{ auth()->id() }}, {{ $volume->id }})"
                                 x-init="checkStatus"
                                 class="flex gap-2 justify-center items-center sm:justify-start sm:items-start ">

                                <template x-if="status === null">
                                    <div class="flex gap-2">
                                        <x-hover-text position="top">
                                            <x-slot name="trigger">
                                                <button @click="addToLibrary"
                                                        class="btn-primary rounded-full p-2 w-10 h-10 font-bold transition-transform duration-100 hover:scale-105 active:scale-95">
                                                    <i class="fa-solid fa-square-plus"></i>
                                                </button>
                                            </x-slot>
                                            {{ __('Add to library') }}
                                        </x-hover-text>

                                        <x-hover-text position="top">
                                            <x-slot name="trigger">
                                                <button @click="addToWishlist"
                                                        class="btn-secondary rounded-full p-2 w-10 h-10 font-bold transition-transform duration-100 hover:scale-105 active:scale-95">
                                                    <i class="fa-solid fa-heart-circle-plus"></i>
                                                </button>
                                            </x-slot>
                                            {{ __('Add to wish list') }}
                                        </x-hover-text>
                                    </div>
                                </template>

                                <template x-if="status === false">
                                    <div class="flex gap-2">
                                        <x-hover-text position="top">
                                            <x-slot name="trigger">
                                                <button @click="addToLibrary"
                                                        class="btn-primary rounded-full p-2 w-10 h-10 font-bold transition-transform duration-100 hover:scale-105 active:scale-95">
                                                    <i class="fa-solid fa-square-plus"></i>
                                                </button>
                                            </x-slot>
                                            {{ __('Move to library') }}
                                        </x-hover-text>

                                        <x-hover-text position="top">
                                            <x-slot name="trigger">
                                                <button @click="remove"
                                                        class="btn-danger rounded-full p-2 w-10 h-10 font-bold
                                   transition-transform duration-100 hover:scale-105 active:scale-95">
                                                    <i class="fa-solid fa-heart-circle-minus"></i>
                                                </button>
                                            </x-slot>
                                            {{ __('Remove from wishlist') }}
                                        </x-hover-text>
                                    </div>
                                </template>

                                <template x-if="status === true">
                                    <x-hover-text position="top">
                                        <x-slot name="trigger">
                                            <button @click="remove"
                                                    class="btn-danger rounded-full p-2 w-10 h-10 font-bold
                               transition-transform duration-100 hover:scale-105 active:scale-95">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </x-slot>
                                        {{ __('Remove from library') }}
                                    </x-hover-text>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
