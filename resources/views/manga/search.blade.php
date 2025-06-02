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
                <x-carrousel
                    name="popular"
                    :items="$popular['popular']"
                    :message="__('The series that are trending among readers')">
                    <x-slot name="title">
                        <i class="fa-solid fa-fire text-orange-500"></i>
                        {{ __('MOST POPULAR SERIES') }}
                        <i class="fa-solid fa-fire text-orange-500"></i>
                    </x-slot>
                </x-carrousel>
            @endif
            @if(!empty($score['score']))
                <x-carrousel
                    name="score"
                    :items="$score['score']"
                    :message="__('The best of the best, choosen by the community')">
                    <x-slot name="title">
                        <i class="fa-solid fa-trophy text-yellow-500"></i>
                        {{ __('HIGHEST RATED SERIES') }}
                        <i class="fa-solid fa-trophy text-yellow-500"></i>
                    </x-slot>
                </x-carrousel>
            @endif
            @if(!empty($trending['trending']))
                <x-carrousel
                    name="trending"
                    :items="$trending['trending']"
                    :message="__('Check the new hits!')">
                    <x-slot name="title">
                        <i class="fa-solid fa-chart-line text-red-600"></i>
                        {{ __('TRENDING SERIES') }}
                        <i class="fa-solid fa-chart-line text-red-600"></i>
                    </x-slot>
                </x-carrousel>
            @endif
        </x-main-container>
    </div>
</x-app-layout>
