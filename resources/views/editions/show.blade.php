{{-- Declaracion de los atributos de esta vista (edition y volumes)--}}
@props([
    'edition',
    'volumes',
])
{{--@dd($volumes, $edition)--}}
<x-app-layout>
    <div class="py-6">
        <x-main-container>
            {{--             Top: imagen / título y autor --}}
            <div class=" flex flex-col sm:flex-row items-center gap-6 mb-2">
                {{-- Imagen con click modal --}}
                <div class="relative w-48 h-max flex-shrink-0">
                    <x-link-button href="{{ route('series.show', ['anilistId' => $edition->series_id, 'slug' => \Illuminate\Support\Str::slug($edition->series->title)]) }}"
                        class="absolute !rounded-full mt-3 top-0 -left-20 sm:-top-14 sm:left-0 xl:top-0 xl:-left-20 z-20"
                        x-data="{ isRotating: false }"
                        @click="isRotating = true;
                                setTimeout(() => isRotating = false, 250)">
                        <i class="fa-solid fa-reply transition-all duration-200 ease-in-out"
                            :class="{ 'rotate-[90deg] text-indigo-500': isRotating }"
                        ></i>
                    </x-link-button>
                    <img src="{!! $volumes[0]->cover_image_url !!}"
                         alt="{!! $edition->localized_title !!} cover img"
                         class="cursor-pointer w-48 h-auto rounded shadow-lg flex-shrink-0"
                         onclick="document.getElementById('my_modal_1').showModal()">
                    <i class="fa-solid fa-magnifying-glass absolute top-3 right-3 text-md rounded-full p-2 text-indigo-500  bg-gray-800/80 dark:bg-white/80 pointer-events-none transition-all duration-200"></i>
                </div>
                {{-- Ventana modal con imagen --}}
                <dialog id="my_modal_1" class="modal">
                    <div class="modal-box bg-transparent">
                        <img src="{!! $volumes[0]->cover_image_url !!}"
                             alt="{!! $edition->localized_title !!} cover img"
                             class="w-full h-auto rounded shadow-lg flex-shrink-0">
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button></button>
                    </form>
                </dialog>

                <div class="text-center md:text-left px-2">
                    <h2 class="text-3xl font-extrabold text-gray-800 dark:text-gray-100">
                        {!! $edition->localized_title !!}
                    </h2>
                    <div class="flex flex-row items-center justify-center sm:justify-start gap-1">
                        <p class="text-lg text-gray-600 dark:text-gray-300">
                            {!! $edition->publisher->name !!}
                        </p>
                        <div class="rounded-full w-5 h-5 bg-cover bg-center flex-shrink-0 m-1"
                             style="background-image: url('/media/lang/{{ $edition->language }}.png');"></div>
                    </div>

                    <x-text class="flex flex-row justify-center sm:justify-start my-1 !p-0 items-center">
{{--                        {!! count($volumes) !!} {{__('volumes')}}--}}
                        <span class="text-xs text-gray-900 dark:text-gray-100 text-right mr-2">{!! $edition->format !!}</span>
                    </x-text>

                    @php
                        // Limite de palabras para mostrar antes del collapse
                        $wordLimit = 20;
                        // Dividir la descripción en dos partes
                        $descriptionParts = explode(' ', ($edition->sinopsis), $wordLimit + 1);
                        $showCollapse = count($descriptionParts) > $wordLimit;
                        $firstPart = implode(' ', array_slice($descriptionParts, 0, $wordLimit));
                        $secondPart = implode(' ', array_slice($descriptionParts, $wordLimit));
                        // Si la descripción es muy corta o no hay, no mostrar el collapse
                        if (strlen($edition->sinopsis) < 50) {
                            $showCollapse = false;
                        } else $firstPart .= '...';
                    @endphp

                    @if($showCollapse)
                        <div class="visible sm:hidden w-full px-0 py-3">
                            <div tabindex="0"
                                 class="visible sm:hidden collapse collapse-plus border-l-4 border-gray-800 dark:border-gray-300 rounded-l-xl px-3 py-2">
                                <x-text
                                    class="!p-0 mx-0 text-left collapse-title font-semibold !pb-0 w-auto text-wrap">
                                    {!! nl2br(e($firstPart)) !!}
                                </x-text>
                                <x-text class="!p-0 text-left mx-0 collapse-content text-sm !pt-1 text-wrap">
                                    {!! nl2br(e($secondPart)) !!}
                                </x-text>
                            </div>
                        </div>
                    @else
                        <x-text
                            class="block text-left sm:hidden border-l-4 border-gray-800 dark:border-gray-300 rounded-l-xl !py-2 mx-3 sm:mx-0">
                            {!! nl2br(e($edition->sinopsis)) !!}
                        </x-text>
                    @endif
                    <x-text
                        class="text-wrap hidden text-left border-l-4 border-gray-800 dark:border-gray-300 rounded-l-xl !py-2 sm:block mx-3 mt-3 sm:mx-0">
                        {!! nl2br(e($edition->sinopsis)) !!}
                    </x-text>
                </div>
            </div>

            {{--   volumenes --}}
            <div class="gap-6 mt-2 sm:mt-5">
                <section class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <x-text class="text-xl font-semibold !py-0">{{__('Volumes for this edition')}}</x-text>
                    @if(count($volumes))
                        <x-text class="!pt-0">
                            {{ count($volumes) }} {{__('volumes')}}
                        </x-text>
                        <div class="flex flex-row flex-wrap gap-y-5 gap-x-12 sm:gap-x-4 justify-center sm:justify-start">
                            @foreach($volumes as $volume)
                                <x-volume-card
                                    :volume="$volume"
                                    :edition="$edition"
                                />
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500">{{ __('There is no volumes available') }}</p>
                    @endif
                </section>
            </div>
        </x-main-container>
    </div>
</x-app-layout>
