@props([
    'volume' => \App\Models\Volume::class // Objeto volumen pasado desde la vista show
])

{{-- Tarjeta de volumen --}}
<div class="border rounded shadow-md bg-white dark:bg-gray-800 flex flex-col items-center">
    {{-- id volume_number total_pages price release_date cover_image_url --}}
    @if($volume->cover_image_url)
        <img src="{{ $volume->cover_image_url }}"
             alt="Nº{{ $volume->volume_number }}"
             class="w-32 h-auto object-cover rounded mb-4">
    @else
        <div class="w-32 h-44 bg-gray-200 dark:bg-gray-700 rounded mb-4 flex items-center justify-center">
            <span class="text-gray-500 dark:text-gray-400 text-sm">No img</span>
        </div>
    @endif
    <div class="flex flex-row items-center gap-2 mb-2">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
             Nº{{ $volume->volume_number }}
        </h3>
        <span class="text-sm text-gray-600 dark:text-gray-400">
            {{ $volume->total_pages }} {{__('pages')}}
        </span>
    </div>
    <p class="text-gray-600 dark:text-gray-300 mb-2">
        {{ $volume->price }} {{__('currency')}}
    </p>
    <p class="text-gray-600 dark:text-gray-300 mb-2">
        {{ $volume->release_date->format('d/m/Y') }}
    </p>

</div>
