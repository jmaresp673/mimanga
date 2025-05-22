@props([
    'position' => 'bottom' // top, bottom, left, right
])

<div
    x-data="{ showTooltip: false }"
    x-on:mouseenter="showTooltip = true"
    x-on:mouseleave="showTooltip = false"
    class="relative inline-block"
    {{ $attributes }}>

    <!-- Elemento que activa el tooltip -->
    <div>
        {{ $trigger }}
    </div>

    <!-- Tooltip -->
    <div
        x-show="showTooltip"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @class([
            'absolute z-[999] px-3 py-2 text-sm text-white bg-gray-800 rounded-md shadow-lg',
            'bottom-full mb-2' => $position === 'top',
            'top-full mt-2' => $position === 'bottom',
            'right-full mr-2' => $position === 'left',
            'left-full ml-2' => $position === 'right'
        ])
        style="min-width: max-content">
        {{ $slot }}
    </div>
</div>


{{--
MODO DE USO:

<x-hover-text position="bottom">
    <x-slot name="trigger"> <!-- Elemento que activa el hover -->
        <button class="p-2 bg-blue-500 text-white">
            Botón con tooltip
        </button>
    </x-slot>

    ¡Este es el mensaje del tooltip!
</x-hover-text>

--}}
