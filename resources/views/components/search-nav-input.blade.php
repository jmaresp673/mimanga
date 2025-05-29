<div class="flex-1 flex items-center dark:text-white justify-center px-2 lg:ml-6">
    <div x-data="{ searchOpen: false }" class="w-full max-w-xl" x-cloak>
        <!-- Fondo difuminado y detector de clicks -->
        <div x-show="searchOpen"
             x-transition.opacity
             class="fixed inset-0 z-40 bg-black/30 dark:bg-gray-900/50 backdrop-blur-sm"
             @click="searchOpen = false">
        </div>

        <!-- Contenedor principal -->
        <div class="relative">
            <!-- Input normal -->
            <div x-show="!searchOpen">
                <input
                    @click="searchOpen = true; $nextTick(() => $refs.searchInput.focus())"
                    type="search"
                    value="{{ request('query') }}"
                    placeholder="{{ __('Search') }}"
                    class="text-gray-500 dark:text-white w-full px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 border-transparent focus:border-gray-400 focus:ring-0 transition-all duration-300 cursor-pointer"
                >
            </div>

            <!-- Input activo -->
            <div x-show="searchOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="fixed z-50 sm:w-full max-w-xl"
                 style="top: 50%; left: 50%; transform: translate(-50%, -50%)">
                <div class="relative" @click.stop>
                    <input
                        x-ref="searchInput"
                        name="query"
                        value="{{ request('query') }}"
                        placeholder="{{ __('What are we reading today?') }}"
                        class="text-black w-full px-4 py-4 text-lg rounded-lg bg-white/95 dark:bg-gray-800/95 dark:text-white backdrop-blur-sm border-transparent focus:ring-2 focus:ring-indigo-400 shadow-lg transition-all duration-200"
                        style="min-width: 300px"
                        x-init="$nextTick(() => $el.focus())"
                        @keydown.escape="searchOpen = false"
                    >

                    <!-- Botón de cerrar -->
                    <div @click="searchOpen = false"
                            class="cursor-pointer absolute -right-4 top-0 flex justify-center items-center rounded-full h-8 w-8 bg-red-600 text-white hover:text-red-600 hover:bg-white text-2xl transition-colors duration-300"
                            style="transform: translateY(-50%)">
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="hidden"></button>
</div>
