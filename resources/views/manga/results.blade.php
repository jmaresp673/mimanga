<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl sm:text-2xl md:text-4xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Results of the search for ') }}<br>
            <strong class="text-2xl sm:text-4xl">{{ $query }}</strong>
        </h2>
    </x-slot>

    <div class="py-6">
        <x-main-container>

            <div id="manga-results" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-6 gap-x-0 sm:gap-x-2">
                @include('manga._cards', ['results' => $results])
            </div>

            {{-- indicador loading --}}
            <div id="loading" class="text-center mt-10 mb-5">
                <div class="inline-flex items-center space-x-2">
                    <div class="mx-auto w-4 h-4 bg-red-500 rounded-full animate-bounce"></div>
                    <div class="mx-auto w-4 h-4 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="mx-auto w-4 h-4 bg-yellow-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
                <x-text class="text-center col-span-full">{{ __('Loading...') }}</x-text>
            </div>

            <!-- boton back -->
            <x-back-button></x-back-button>
        </x-main-container>
    </div>

    <!-- Infinite Scroll Script -->
    <script>
        (function () {
            let page = {{ $pageInfo['currentPage'] ?? 1 }};
            let hasNext = {{ $pageInfo['hasNextPage'] ? 'true' : 'false' }};
            const query = @json($query);
            let loading = false;

            const container = document.getElementById('manga-results');
            const loader = document.getElementById('loading');

            window.addEventListener('scroll', () => {
                if (loading || !hasNext) return;
                if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 200) {
                    loadMore();
                }
            });

            function loadMore() {
                loading = true;
                loader.classList.remove('hidden');
                page++;

                fetch(`{{ route('manga.search.dynamic') }}?query=${encodeURIComponent(query)}&page=${page}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(r => r.text())
                    .then(html => {
                        // 1) Si la respuesta tiene el mensaje final...
                        if (html.includes('id="no-more-results"')) {
                            // Sólo añadirlo si aún no existe
                            if (!document.getElementById('no-more-results')) {
                                container.insertAdjacentHTML('beforeend', html);
                            }
                            hasNext = false;
                        } else {
                            // 2) Si no hay mensaje final, insertar todas las tarjetas
                            container.insertAdjacentHTML('beforeend', html);
                        }
                    })
                    .catch(console.error)
                    .finally(() => {
                        loading = false;
                        loader.classList.add('hidden');
                    });
            }
        })();
    </script>


</x-app-layout>
