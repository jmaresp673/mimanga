@props([
    'cover',        // URL de la portada
    'title',        // Título localizado de la edición
    'lang',         // Código de idioma, p.ej. 'ES', 'EN'
    'count'         // Cantidad de volúmenes editados
])

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
    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100 text-center">
        {{ $title }}
    </h4>
    <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ $lang }} &middot; {{ $count }}
        <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4" fill="none" viewBox="0 0 24 24"
             stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
    </p>
</div>
