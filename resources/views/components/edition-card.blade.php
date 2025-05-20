@props([
    'id',           // ID de la edición
    'cover',        // URL de la portada
    'title',        // Título localizado de la edición
    'lang',         // Código de idioma, p.ej. 'ES', 'EN'
    'count'         // Cantidad de volúmenes editados
])

<a href="{{ route('editions.show', ['id' => $id, 'slug' => \Illuminate\Support\Str::slug($title)]) }}"
   class="group w-full h-full flex flex-col items-center justify-center text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-200 ease-in-out">
    {{-- Tarjeta de edición --}}
    <div class="border rounded p-4 shadow-md bg-white dark:bg-gray-800 flex flex-col items-center">
        {{-- Portada del primer volumen --}}
        @if($cover)
            <img src="{{ $cover }}"
                 alt="Portada de {{ $title }}"
                 class="w-32 h-auto object-cover rounded mb-4">
        @else
            <div class="w-32 h-44 bg-gray-200 dark:bg-gray-700 rounded mb-4 flex items-center justify-center">
                <span class="text-gray-500 dark:text-gray-400 text-sm">No img</span>
            </div>
        @endif

        {{-- Información de la edición completa --}}
        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100 text-center mb-2">
            {{ $title }}
        </h4>

        <div class="flex flex-row text-sm gap-2 justify-center items-center text-gray-600 dark:text-gray-400">
            <div class="rounded-full w-5 h-5 bg-cover bg-center"
                 style="background-image: url('/media/lang/{{ $lang }}.png');"></div>
            &middot; {{ $count }}
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </div>
    </div>
</a>
