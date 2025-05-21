{{-- Declaracion de los atributos de esta vista (edition y volumes)--}}
@props([
    'edition',
    'volumes',
])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    });
</script>
<x-app-layout>

    {{--    <x-slot name="header">--}}
    {{--        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">--}}
    {{--            {!! $edition->localized_title !!}--}}
    {{--        </h1>--}}
    {{--    </x-slot>--}}

    <div class="py-6">
        <x-main-container>
            {{--             Top: imagen / título y autor --}}
            <div class="flex flex-col sm:flex-row items-center gap-6 mb-8">
                <img src="{!! $volumes[0]->cover_image_url !!}"
                     alt="{!! $edition->localized_title !!} cover img"
                     class="w-48 h-auto rounded shadow-lg flex-shrink-0">

                <div class="text-center md:text-left">
                    <div class="flex flex-row items-center gap-2">
                        <h2 class="text-3xl font-extrabold text-gray-800 dark:text-gray-100">
                            {!! $edition->localized_title !!}

                        </h2>
                        <div class="rounded-full w-5 h-5 bg-cover bg-center"
                             style="background-image: url('/media/lang/{{ $edition->language }}.png');"></div>
                    </div>
                    <p class="text-lg text-gray-600 dark:text-gray-300">
                        {!! $edition->publisher->name !!}
                    </p>
                    <x-text class="flex flex-row justify-between mt-1 !p-0 items-center">
                        {!! $edition->edition_total_volumes !!} volumenes<span
                            class="text-xs text-gray-300">{!! $edition->format !!}</span>
                    </x-text>
                    <x-text class="text-justify">
                        @foreach(preg_split('/\s{2,}|\r?\n\r?\n/', $edition->sinopsis) as $parrafo)
                            {{ $parrafo }}<br><br>
                        @endforeach
                    </x-text>
                </div>
            </div>

            {{--   volumenes --}}
            <div class="gap-6">

                <section class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <x-text class="text-xl font-semibold mb-4">{{__('Editions for this series')}}</x-text>
                    {{--                    @dd($general, $editions)--}}
                    @if(count($volumes))
                        <x-text>
                            {{ count($volumes) }} {{__('volumes')}}
                        </x-text>
                        <div class="flex flex-row flex-wrap gap-4 justify-center">
                            @foreach($volumes as $volume)
                                <x-volume-card
                                    :volume="$volume"
                                    :edition="$edition"
                                />
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500">No hay volumenes disponibles.</p>
                    @endif
                </section>
            </div>
        </x-main-container>
    </div>
</x-app-layout>
