@props([
    'popular' => [],
    'score' => [],
    'trending' => [],
])
<x-app-layout>
    <div class="py-6">
        <x-main-container>
            <form method="POST" action="{{ route('manga.search.perform') }}"
                  class="flex flex-col items-center gap-4 mb-16 sm:mb-8 hover:scale-105 transition-all duration-200">
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

                <div class="h-min">
                    <x-text class="text-2xl font-bold ml-3 !pb-0 sm:text-center">
                        <i class="fa-solid fa-fire text-orange-500"></i>
                        {{ __('MOST POPULAR SERIES') }}
                        <i class="fa-solid fa-fire text-orange-500"></i>
                    </x-text>
                    <x-text class="text-md font-bold !text-gray-400 ml-3 !py-0 sm:text-center">
                     {{ __('The series that are trending among readers') }} {{--   {{ __('Las series que están arrasando entre los lectores') }}--}}
                    </x-text>

                    {{-- Desktop (>=1024px): 4 por slide --}}
                    <div class="carousel py-5 h-min w-full hidden lg:flex">
                        @foreach($chunksLg as $index => $chunk)
                            <div id="popular-lg-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="grid h-min grid-cols-4 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                                <div
                                    class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2 pointer-events-none">
                                    <a href="#popular-lg-slide{{ $index === 0 ? count($chunksLg) : $index }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❮</a>
                                    <a href="#popular-lg-slide{{ $index + 2 > count($chunksLg) ? 1 : $index + 2 }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❯</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tablet (>=768px <1024px): 3 por slide --}}
                    <div class="carousel pt-5 h-min w-full hidden md:flex lg:hidden">
                        @foreach($chunksMd as $index => $chunk)
                            <div id="popular-md-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="grid h-min grid-cols-3 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                                <div
                                    class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2 pointer-events-none">
                                    <a href="#popular-md-slide{{ $index === 0 ? count($chunksMd) : $index }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❮</a>
                                    <a href="#popular-md-slide{{ $index + 2 > count($chunksMd) ? 1 : $index + 2 }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❯</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Mobile (<768px): 2 por slide --}}
                    <div class="carousel pt-5 h-min w-full flex md:hidden">
                        @foreach($chunksSm as $index => $chunk)
                            <div id="popular-sm-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="h-min grid grid-cols-2 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if(!empty($score['score']))
                @php
                    $chunksLg = array_chunk($score['score'], 4);
                    $chunksMd = array_chunk($score['score'], 3);
                    $chunksSm = array_chunk($score['score'], 2);
                @endphp

                <div class="h-min">
                    <x-text class="text-2xl font-bold ml-3 !pb-0 sm:text-center">
                        <i class="fa-solid fa-trophy text-yellow-500"></i>
                        {{ __('Best rated series') }}
                        <i class="fa-solid fa-trophy text-yellow-500"></i>
                    </x-text>
                    <x-text class="text-md font-bold !text-gray-400 ml-3 !py-0 sm:text-center">
                        {{ __('The best of the best, choosen by the community') }}
                    </x-text>

                    {{-- Desktop (>=1024px): 4 por slide --}}
                    <div class="carousel py-5 h-min w-full hidden lg:flex">
                        @foreach($chunksLg as $index => $chunk)
                            <div id="score-lg-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="grid h-min grid-cols-4 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                                <div
                                    class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2 pointer-events-none">
                                    <a href="#score-lg-slide{{ $index === 0 ? count($chunksLg) : $index }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❮</a>
                                    <a href="#score-lg-slide{{ $index + 2 > count($chunksLg) ? 1 : $index + 2 }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❯</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tablet (>=768px <1024px): 3 por slide --}}
                    <div class="carousel pt-5 h-min w-full hidden md:flex lg:hidden">
                        @foreach($chunksMd as $index => $chunk)
                            <div id="score-md-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="grid h-min grid-cols-3 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                                <div
                                    class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2 pointer-events-none">
                                    <a href="#score-md-slide{{ $index === 0 ? count($chunksMd) : $index }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❮</a>
                                    <a href="#score-md-slide{{ $index + 2 > count($chunksMd) ? 1 : $index + 2 }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❯</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Mobile (<768px): 2 por slide --}}
                    <div class="carousel pt-5 h-min w-full flex md:hidden">
                        @foreach($chunksSm as $index => $chunk)
                            <div id="score-sm-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="h-min grid grid-cols-2 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if(!empty($trending['trending']))
                @php
                    $chunksLg = array_chunk($trending['trending'], 4);
                    $chunksMd = array_chunk($trending['trending'], 3);
                    $chunksSm = array_chunk($trending['trending'], 2);
                @endphp

                <div class="h-min">
                    <x-text class="text-2xl font-bold ml-3 !pb-0 sm:text-center">
                        <i class="fa-solid fa-arrow-trend-up text-red-600"></i>
                        {{ __('Top trending series') }}
                        <i class="fa-solid fa-arrow-trend-up text-red-600"></i>
                    </x-text>
                    <x-text class="text-md font-bold !text-gray-400 ml-3 !py-0 sm:text-center">
                        {{ __('Check the new hits!') }}
                    </x-text>

                    {{-- Desktop (>=1024px): 4 por slide --}}
                    <div class="carousel py-5 h-min w-full hidden lg:flex">
                        @foreach($chunksLg as $index => $chunk)
                            <div id="trending-lg-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="grid h-min grid-cols-4 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                                <div
                                    class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2 pointer-events-none">
                                    <a href="#trending-lg-slide{{ $index === 0 ? count($chunksLg) : $index }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❮</a>
                                    <a href="#trending-lg-slide{{ $index + 2 > count($chunksLg) ? 1 : $index + 2 }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❯</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tablet (>=768px <1024px): 3 por slide --}}
                    <div class="carousel pt-5 h-min w-full hidden md:flex lg:hidden">
                        @foreach($chunksMd as $index => $chunk)
                            <div id="trending-md-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="grid h-min grid-cols-3 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                                <div
                                    class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2 pointer-events-none">
                                    <a href="#trending-md-slide{{ $index === 0 ? count($chunksMd) : $index }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❮</a>
                                    <a href="#trending-md-slide{{ $index + 2 > count($chunksMd) ? 1 : $index + 2 }}"
                                       class="btn btn-circle bg-indigo-600 text-white hover:bg-indigo-700 active:scale-75 active:bg-indigo-400 transform transition-all duration-200 pointer-events-auto">❯</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Mobile (<768px): 2 por slide --}}
                    <div class="carousel pt-5 h-min w-full flex md:hidden">
                        @foreach($chunksSm as $index => $chunk)
                            <div id="trending-sm-slide{{ $index + 1 }}" class="carousel-item h-min relative w-full">
                                <div class="h-min grid grid-cols-2 gap-6 mx-auto">
                                    @foreach($chunk as $manga)
                                        @include('manga._card', ['manga' => $manga])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-main-container>
    </div>
</x-app-layout>
