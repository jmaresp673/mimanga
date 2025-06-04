@props([
    'name' => 'carrousel',
    'items' => [],
    'message' => null,
    'title' => null,
    ])

{{-- Ejemplo de uso --}}
{{--<x-carrousel--}}
{{--    name="popular"--}}
{{--    :items="$popular['popular']"--}}
{{--    :message="__('The series that are trending among readers')">--}}
{{--    <x-slot name="title">--}}
{{--        <i class="fa-solid fa-fire text-orange-500"></i>--}}
{{--        {{ __('MOST POPULAR SERIES') }}--}}
{{--        <i class="fa-solid fa-fire text-orange-500"></i>--}}
{{--    </x-slot>--}}
{{--</x-carrousel>--}}

@php
    $chunksLg = array_chunk($items, 4);
    $chunksMd = array_chunk($items, 3);
    $chunksSm = array_chunk($items, 2);
@endphp

<div class="h-min">
    <x-text class="text-xl sm:text-2xl font-bold ml-3 !pb-0 sm:text-center">
        {{ $title ?? $name }}
    </x-text>
    <x-text class="text-md font-bold !text-gray-400 ml-3 !py-0 sm:text-center">
        {{ $message ?? $name }}
    </x-text>

    {{-- Desktop (>=1024px): 4 por slide --}}
    <div class="carousel py-5 h-min w-full hidden lg:flex">
        @foreach($chunksLg as $index => $chunk)
            <div id="{{$name}}-lg-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                <div class="grid h-min grid-cols-4 gap-6 mx-auto">
                    @foreach($chunk as $manga)
                        @include('manga._card', ['manga' => $manga])
                    @endforeach
                </div>
                <div
                    class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2 pointer-events-none">
                    <a href="#{{$name}}-lg-slide{{ $index === 0 ? count($chunksLg) : $index }}"
                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❮</a>
                    <a href="#{{$name}}-lg-slide{{ $index + 2 > count($chunksLg) ? 1 : $index + 2 }}"
                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❯</a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Tablet (>=768px <1024px): 3 por slide --}}
    <div class="carousel pt-5 h-min w-full hidden md:flex lg:hidden">
        @foreach($chunksMd as $index => $chunk)
            <div id="{{$name}}-md-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                <div class="grid h-min grid-cols-3 gap-4 mx-auto">
                    @foreach($chunk as $manga)
                        @include('manga._card', ['manga' => $manga])
                    @endforeach
                </div>
                <div
                    class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2 pointer-events-none">
                    <a href="#{{$name}}-md-slide{{ $index === 0 ? count($chunksMd) : $index }}"
                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❮</a>
                    <a href="#{{$name}}-md-slide{{ $index + 2 > count($chunksMd) ? 1 : $index + 2 }}"
                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❯</a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Mobile (<768px): 2 por slide --}}
    <div class="carousel pt-4 h-min w-full flex md:hidden">
        @foreach($chunksSm as $index => $chunk)
            <div id="{{$name}}-sm-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                <div class="h-min grid grid-cols-2 gap-2 mb-3 mx-auto">
                    @foreach($chunk as $manga)
                        @include('manga._card', ['manga' => $manga])
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
