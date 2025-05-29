@props(['disabled' => false])
<div class="relative w-3/4 flex justify-center">
    <input name="query" value="{{ request('query') }}" @disabled($disabled) {{ $attributes->merge(['class' => 'text-black w-full transition-all duration-200 p-4 pl-12 rounded-xl border-2 border-indigo-300 shadow-comic focus:ring-4 focus:ring-indigo-200 focus:border-indigo-500 text-lg']) }}>
    {{-- Icono de búsqueda --}}
    <div class="absolute left-4 top-4 text-indigo-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>
    {{-- Botón de búsqueda si se define --}}
    {{ $slot }}
</div>
