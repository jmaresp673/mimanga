@props([
    'popular' => []
])
<x-app-layout>
{{--    <x-slot name="header">--}}
{{--        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">--}}
{{--            {{ __('Search') }}--}}
{{--        </h2>--}}
{{--    </x-slot>--}}

    <div class="py-6">
        <x-main-container>
            <form method="POST" action="{{ route('manga.search.perform') }}" class="flex flex-col items-center gap-4">
                @csrf
                <x-search-input name="query" placeholder="{{ __('What are we reading today? ') }}" required>
                    <x-search-button>
                        {{ __('Search') }}
                    </x-search-button>
                </x-search-input>
            </form>
            @if(!empty($popular['popular']))
                @php
                    $chunksLg = array_chunk($popular['popular'], 4);
                    $chunksMd = array_chunk($popular['popular'], 3);
                    $chunksSm = array_chunk($popular['popular'], 2);
                @endphp

                <div class="mt-16 md:mt-12 h-min">
                    <x-text class="text-2xl font-bold mb-6 ml-3 sm:text-center">
                        {{ __('Popular Manga') }}
                    </x-text>

                    {{-- Desktop (>=1024px): 4 por slide --}}
                    <div class="carousel py-5 h-min w-full hidden lg:flex">
                        @foreach($chunksLg as $index => $chunk)
                            <div id="lg-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="grid h-min grid-cols-4 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                                <div class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                    <a href="#lg-slide{{ $index === 0 ? count($chunksLg) : $index }}" class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200">❮</a>
                                    <a href="#lg-slide{{ $index + 2 > count($chunksLg) ? 1 : $index + 2 }}" class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200">❯</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tablet (>=768px <1024px): 3 por slide --}}
                    <div class="carousel py-5 h-min w-full hidden md:flex lg:hidden">
                        @foreach($chunksMd as $index => $chunk)
                            <div id="md-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="grid h-min grid-cols-3 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                                <div class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                    <a href="#md-slide{{ $index === 0 ? count($chunksMd) : $index }}" class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200">❮</a>
                                    <a href="#md-slide{{ $index + 2 > count($chunksMd) ? 1 : $index + 2 }}" class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200">❯</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Mobile (<768px): 2 por slide --}}
                    <div class="carousel py-5 h-min w-full flex md:hidden">
                        @foreach($chunksSm as $index => $chunk)
                            <div id="sm-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="h-min grid grid-cols-2 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                                <div class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                    <a href="#sm-slide{{ $index === 0 ? count($chunksSm) : $index }}" class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200">❮</a>
                                    <a href="#sm-slide{{ $index + 2 > count($chunksSm) ? 1 : $index + 2 }}" class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200">❯</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-main-container>
    </div>
</x-app-layout>
