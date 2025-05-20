@props([
    'volume' => \App\Models\Volume::class,
    'edition' => \App\Models\Edition::class
])

<div class="cursor-pointer relative px-2 rounded shadow-md bg-white dark:bg-gray-800 flex flex-col items-center text-center w-32 h-48
            overflow-hidden group transition-all duration-300 ease-in-out"
     style="background-image: url('{{ $volume->cover_image_url }}'); background-size: cover; background-position: center;">

    <!-- Capa de información -->
    <div class="absolute bottom-0 w-full bg-gray-950 bg-opacity-60
                transition-all duration-300 ease-in-out flex items-center
                min-h-[4rem] group-hover:min-h-full">
        <div class="p-2 flex flex-col justify-center h-full space-y-1">
            <!-- Título siempre centrado -->
            <h3 class="text-sm font-semibold text-gray-100 transition-all px-1">
                {{$edition->localized_title}} Nº{{ $volume->volume_number }}
            </h3>

            <!-- Contenido adicional con aparición suave -->
            <div class="opacity-0 max-h-0 group-hover:opacity-100 group-hover:max-h-96
                       transition-all duration-300 overflow-hidden
                       flex flex-col items-center justify-center text-center">
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
</div>
