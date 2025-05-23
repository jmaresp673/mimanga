{{-- Declaracion de los atributos de esta vista (edition y volumes)--}}
@props([
    'volumes' => [],
    'whislist' => []
])
<x-app-layout>

    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            {{ __('Library') }}
        </h1>
    </x-slot>

    <div class="py-6 container mx-auto">
        <x-main-container>
            {{--   volumenes --}}
            <div class="gap-6">

                <section class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                    @if($volumes->isNotEmpty())
                        <div class="flex flex-col items-center justify-center h-full">
                            <x-text class="text-xl pt-0">
                                {{ count($volumes) }} {{__('volumes in your library')}}
                            </x-text>
                            <div class="flex flex-row flex-wrap gap-4 justify-center">
                                @foreach($volumes as $volume)
                                    <x-volume-library
                                        :volume="$volume"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-full">
                            <x-no-more-results>
                                {{ __('Your library is currently empty, explore new series:') }}
                            </x-no-more-results>
                            <x-link-button href="{{ route('manga.search') }}">
                                {{ __('Explore') }}
                            </x-link-button>
                        </div>
                    @endif
                    {{--                    @if(count($volumes))--}}
                    {{--                        <x-text>--}}
                    {{--                            {{ count($volumes) }} {{__('volumes')}}--}}
                    {{--                        </x-text>--}}
                    {{--                        <div class="flex flex-row flex-wrap gap-4 justify-center">--}}
                    {{--                            @foreach($volumes as $volume)--}}
                    {{--                                <x-volume-card--}}
                    {{--                                    :volume="$volume"--}}
                    {{--                                    :edition="$edition"--}}
                    {{--                                />--}}
                    {{--                            @endforeach--}}
                    {{--                        </div>--}}
                    {{--                    @else--}}
                    {{--                        <p class="text-center text-gray-500">No hay volumenes disponibles.</p>--}}
                    {{--                    @endif--}}
                </section>
            </div>
        </x-main-container>
        <x-main-container class="mt-6">
            <div class="gap-6">
                <section class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                    @if($whislist->isNotEmpty())
                        <div class="flex flex-col items-center justify-center h-full">
                            <x-text class="text-xl pt-0">
                                {{ count($whislist) }} {{__('volumes in your whislist')}}
                            </x-text>
                            <div class="flex flex-row flex-wrap gap-4 justify-center">
                                @foreach($whislist as $volume)
                                    <x-volume-library
                                        :volume="$volume"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-full">
                            <x-no-more-results>
                                {{ __('Your whislist is currently empty') }}
                            </x-no-more-results>
                        </div>
                    @endif
                    {{--                    @if(count($volumes))--}}
                    {{--                        <x-text>--}}
                    {{--                            {{ count($volumes) }} {{__('volumes')}}--}}
                    {{--                        </x-text>--}}
                    {{--                        <div class="flex flex-row flex-wrap gap-4 justify-center">--}}
                    {{--                            @foreach($volumes as $volume)--}}
                    {{--                                <x-volume-card--}}
                    {{--                                    :volume="$volume"--}}
                    {{--                                    :edition="$edition"--}}
                    {{--                                />--}}
                    {{--                            @endforeach--}}
                    {{--                        </div>--}}
                    {{--                    @else--}}
                    {{--                        <p class="text-center text-gray-500">No hay volumenes disponibles.</p>--}}
                    {{--                    @endif--}}
                </section>
            </div>
        </x-main-container>
    </div>
</x-app-layout>
