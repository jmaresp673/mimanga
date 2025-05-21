@props([
    'volume' => \App\Models\Volume::class,
    'edition' => \App\Models\Edition::class
])
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    });
</script>

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

        <div class="p-2 flex flex-col justify-center h-full space-y-1">
            <h3 class="text-sm font-semibold text-gray-100 px-1">
                {{$edition->localized_title}} Nº{{ $volume->volume_number }}
            </h3>

            <div class="opacity-0 max-h-0
                       transition-all duration-300 overflow-hidden
                       flex flex-col items-center justify-center text-center"
                 :class="isHovered && !openModal ? 'opacity-100 max-h-96' : ''">
                <span class="text-xs text-gray-300">
                    {{ $volume->total_pages }} {{__('pages')}}
                </span>
                <p class="text-xs text-gray-300">
                    {{ $volume->price }} €
                </p>
                <p class="text-xs text-gray-300">
                    {{ $volume->release_date->format('d/m/Y') }}
                </p>
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
                 class="bg-gray-800 rounded-t-xl sm:rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transform transition-all"
                 @click.stop
                 x-cloak>

                <!-- Cabecera -->
                <div class="p-4 border-b border-gray-700 flex justify-between items-center bg-gray-900">
                    <div>
                        <h3 class="text-xl font-bold text-white">
                            <span>{{ $volume->volume_number }}</span>
                            -
                            <span>{{$edition->localized_title}}</span>
                        </h3>
                        <p class="text-sm text-gray-400 mt-1">
                            {{__('Edition')}}: {{$edition->publisher->name}}
                        </p>
                    </div>
                    <x-hover-text position="left">
                        <x-slot name="trigger">
                            <button x-on:click="openModal = false; isHovered = false"
                                    class="relative flex justify-center items-center rounded-full h-8 w-8 bg-red-600 text-white hover:text-red-600 hover:bg-white text-2xl transition-colors duration-300">
                                &times;
                            </button>
                        </x-slot>
                        {{ __('Close') }}
                    </x-hover-text>
                </div>

                <!-- Contenido -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-white">
                        <!-- Portada -->
                        <div class="col-span-1 flex flex-col gap-2">
                            <img src='{{ $volume->cover_image_url }}'
                                 class="w-full h-52 object-cover rounded-lg shadow-xl"
                                 alt="Portada del volumen">
                            <div class="text-center">
                                <p class="text-lg font-semibold"> {{ $volume->price }} €</p>
                            </div>
                        </div>

                        <!-- Detalles -->
                        <div class="col-span-2 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-400 mb-1">{{__('Release Date')}}</h4>
                                    <p> {{ $volume->release_date->format('d/m/Y') }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-semibold text-gray-400 mb-1">{{__('Pages')}}</h4>
                                    <p> {{ __('total pages: ') }}{{ $volume->total_pages }}</p>
                                </div>
                            </div>

                            {{-- Botones de accion --}}
                            <div x-data="{status: null,
                            async checkStatus() {
                                const response = await fetch(`/users/{{ auth()->id() }}/volumes/{{ $volume->id }}/check-status`);
                                const data = await response.json();
                                this.status = data.status;
                            },
                            async addToLibrary() {
                                await fetch(`/users/{{ auth()->id() }}/volumes/{{ $volume->id }}/add-to-library`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': window.csrfToken,
                                        'Content-Type': 'application/json'
                                    }
                                }); await this.checkStatus();},
                            async addToWishlist() {
                                await fetch(`/users/{{ auth()->id() }}/volumes/{{ $volume->id }}/add-to-wishlist`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': window.csrfToken,
                                        'Content-Type': 'application/json'
                                    }
                                }); await this.checkStatus(); },
                            async remove() {
                                await fetch(`/users/{{ auth()->id() }}/volumes/{{ $volume->id }}/remove`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': window.csrfToken,
                                        'Content-Type': 'application/json'
                                    }
                                }); await this.checkStatus(); }
                        }" x-init="checkStatus">
                                <!-- Botones Dinámicos -->
                                <template x-if="status === null">
                                    <div class="space-x-2">
                                        <x-hover-text>
                                            <x-slot name="trigger">
                                                <button @click="addToLibrary"
                                                        class="btn-primary rounded-full p-2 w-10 h-10 font-bold">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="h-5 w-5 inline m-auto" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </button>
                                            </x-slot>
                                            {{ __('Add to library') }}
                                        </x-hover-text>
                                        <x-hover-text>
                                            <x-slot name="trigger">
                                                <button @click="addToWishlist"
                                                        class="btn-secondary rounded-full p-2 w-10 h-10 font-bold">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="h-5 w-5 inline m-auto" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 21.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
                                                    </svg>
                                                </button>
                                            </x-slot>
                                            {{ __('Add to wish list') }}
                                        </x-hover-text>
                                    </div>
                                </template>

                                <template x-if="status === false">
                                    <div class="space-x-2">
                                        <x-hover-text>
                                            <x-slot name="trigger">
                                                <button @click="addToLibrary"
                                                        class="btn-primary rounded-full p-2 w-10 h-10 font-bold">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="h-5 w-5 inline m-auto"
                                                         fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </button>
                                            </x-slot>
                                            {{ __('Add to library') }}
                                        </x-hover-text>
                                        <x-hover-text>
                                            <x-slot name="trigger">
                                                <button @click="remove"
                                                        class="btn-danger rounded-full p-2 w-10 h-10 font-bold">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="h-5 w-5 inline m-auto"
                                                         fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </x-slot>
                                            {{ __('Remove from wish list') }}
                                        </x-hover-text>
                                    </div>
                                </template>

                                <template x-if="status === true">
                                    <x-hover-text>
                                        <x-slot name="trigger">
                                            <button @click="remove"
                                                    class="btn-danger rounded-full p-2 w-10 h-10 font-bold">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline m-auto"
                                                     fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
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
