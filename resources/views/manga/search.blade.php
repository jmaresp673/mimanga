<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Search') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <x-main-container>
            <form method="POST" action="{{ route('manga.search.perform') }}" class="flex flex-col items-center gap-4">
                @csrf
                <input type="text" name="query" class="border rounded p-2 w-1/2" placeholder="{{ __('What are we reading today? ') }}" required>
                <x-primary-button>
                    {{ __('Search') }}
                </x-primary-button>
            </form>
        </x-main-container>
    </div>
</x-app-layout>
