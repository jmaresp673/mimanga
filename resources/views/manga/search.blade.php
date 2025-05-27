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
        </x-main-container>
    </div>
</x-app-layout>
