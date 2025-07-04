<div x-data="{ show: false }"
     x-init="setTimeout(() => show = true, 10)"
     x-show="show"
     x-transition:enter="transition-opacity duration-1500"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     id="no-more-results"
     class="text-center py-8 col-span-full">
    <div class="max-w-xs sm:max-w-md mx-auto p-6 bg-indigo-100 dark:bg-gray-700 rounded-xl shadow-comic dark:shadow-[5px_5px_0px_rgba(99,102,241,0.35)]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-indigo-500" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 12m-9 0a9 9 0 1118 0 9 9 0 11-18 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 9l-6 6m0-6l6 6"/>
        </svg>
        <p class="mt-2 text-indigo-600 dark:text-indigo-300">{{$slot}}</p>
    </div>
</div>
