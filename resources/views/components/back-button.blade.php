<div class="text-center px-4">
    <x-link-button href="{{ route('manga.search') }}"
                   class="inline-flex items-center px-6 py-3 bg-white dark:bg-gray-800 border-2 border-indigo-500
                                      text-indigo-600 dark:text-indigo-300 font-bold rounded-lg shadow-comic
                                      hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        {{ __('Go back') }}
    </x-link-button>
</div>
