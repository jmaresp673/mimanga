@props([
    'id',           // ID de la edición
    'cover',        // URL de la portada
    'title',        // Título localizado de la edición
    'lang',         // Código de idioma, p.ej. 'ES', 'EN'
    'count',         // Cantidad de volúmenes editados
    'publisher'     // Nombre del editor
])

<a href="{{ route('editions.show', ['id' => $id, 'slug' => \Illuminate\Support\Str::slug($title)]) }}"
   class="group border shadow-md mx-auto rounded-lg w-fit h-full flex flex-col items-center justify-start text-gray-800 dark:text-gray-100 bg-white/50 hover:bg-gray-100  dark:bg-gray-800/70 dark:hover:bg-gray-800 transition-all duration-200">
    {{-- Tarjeta de edición --}}
    <div class="rounded-lg p-4 flex flex-col items-center">
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
        <h4 class="text-lg text-wrap font-semibold text-gray-800 dark:text-gray-100 text-center mb-2">
            {{ $title }}
        </h4>

        <div class="flex flex-row text-sm gap-2 justify-center items-center text-gray-600 dark:text-gray-400">
            <div class="line-clamp-1 rounded-full w-5 h-5 bg-cover bg-center flex-shrink-0"
                 style="background-image: url('/media/lang/{{ $lang }}.png');"></div>
            &middot; {{ $count }}
            &middot; {{ $publisher }}
        </div>
    </div>
</a>
