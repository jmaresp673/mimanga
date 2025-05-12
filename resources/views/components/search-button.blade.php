<button {{ $attributes->merge(['type' => 'submit', 'class' => 'absolute right-4 top-3 px-6 py-2 bg-indigo-500 dark:bg-indigo-700 hover:bg-indigo-700
                                                   text-white font-bold rounded-lg shadow-comic-button transform
                                                   hover:scale-105 active:scale-100 transition-all duration-400']) }}>
    {{ $slot }}
</button>
