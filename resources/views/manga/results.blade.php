<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Results of the search for ') }}<strong>{{ $query }}</strong>
        </h2>
    </x-slot>

    <div class="py-6">
        <x-main-container>
            <form method="POST" action="{{ route('manga.search.perform') }}"
                  class="flex flex-col items-center gap-4 mb-10">
                @csrf
                <input type="text" name="query" class="border rounded p-2 w-1/2"
                       placeholder="{{ __('What are we reading today? ') }}" required>
                <x-primary-button>
                    {{ __('Search') }}
                </x-primary-button>
            </form>
            <div id="manga-results" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @include('manga._cards', ['results' => $results])
            </div>

            <div id="loading" class="text-center my-6 hidden">
                <p class="text-gray-500">{{__('Loading. . .')}}</p>
            </div>

            <div class="mt-6 text-center">
                <x-link-button href="{{ route('manga.search') }}">
                    Volver a buscar
                </x-link-button>
            </div>
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
                        // 1) Si la respuesta incluye el mensaje final...
                        if (html.includes('id="no-more-results"')) {
                            // Sólo añadirlo si aún no existe
                            if (!document.getElementById('no-more-results')) {
                                container.insertAdjacentHTML('beforeend', html);
                            }
                            hasNext = false;
                        } else {
                            // 2) Si no hay mensaje final, simplemente insertar todas las tarjetas
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
