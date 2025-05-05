<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Author') }}: {{ $author->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <x-main-container>
            <div class="bg-white dark:bg-gray-800 shadow rounded p-6">
                <img src="{{ $author->image }}" alt="{{ $author->name }}" class="w-32 h-32 rounded-full mx-auto mb-4">
                <x-text><strong>{{ __('Name: ') }}</strong> {{ $extendedData['name']['full'] ?? 'Unknow' }}</x-text>
                @if(!empty($extendedData['name']['native']))
                    <x-text><strong>{{ __('Native name: ') }}</strong> {{ $extendedData['name']['native'] }}</x-text>
                @endif
                @if(!empty($extendedData['age']))
                    <x-text><strong>{{ __('Age: ') }}</strong> {{ $extendedData['age'] }} {{ __('years') }}</x-text>
                @endif
                @if(!empty($extendedData['gender']))
                    <x-text><strong>{{ __('Gender: ') }}</strong> {{ $extendedData['gender'] }}</x-text>
                @endif
                @if(!empty($extendedData['dateOfBirth']['year']))
                    <x-text><strong>{{ __('Birth: ') }}</strong>
                        {{ sprintf('%02d-%02d-%04d',
                            $extendedData['dateOfBirth']['day'] ?? 0,
                            $extendedData['dateOfBirth']['month'] ?? 0,
                            $extendedData['dateOfBirth']['year']
                        ) }}
                    </x-text>
                @endif
            </div>
        </x-main-container>
    </div>
</x-app-layout>
