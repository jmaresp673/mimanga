@php
    // Renderiza el boton en ingles o español segun el idioma actual (locale)
    $locale = app()->getLocale();
@endphp
@if($locale === "es")
    <a href="{{ route('change.language', ['locale' => 'en']) }}"
       class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
        <span class="flex items-start">
            <img src="/media/lang/EN.png" alt="English" class="rounded-full w-4 h-4 my-auto inline">
            <span class="ms-2">Switch to English</span>
        </span>
    </a>
@elseif($locale === "en")
    <a href="{{ route('change.language', ['locale' => 'es']) }}"
       class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
        <span class="flex items-start">
            <img src="/media/lang/ES.png" alt="Español" class="rounded-full w-4 h-4 my-auto inline">
            <span class="ms-2">Cambiar a Español</span>
        </span>
    </a>
@endif
