<nav x-data="{ open: false, searchOpen: false }"
     class="bg-white dark:bg-gray-800 sm:mb-10 border-b container 2xl:max-w-7xl sm:rounded-full sm:mx-auto border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class=" max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-around sm:justify-between h-16">
            <div class="flex justify-between">
                <!-- Logo -->
                <div class="shrink-0 flex items-center ">
                    <a href="{{ route('manga.search') }}">
                        <x-application-logo
                            class="block p-2 h-16 w-auto fill-current text-gray-800 dark:text-gray-200"/>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('manga.search')" :active="request()->routeIs('manga.search')">
                        <i class="fa-solid fa-home size-[1.2em]"></i>
                        <span class="ml-2">{{ __('Home') }}</span>
                    </x-nav-link>
                    <x-nav-link :href="route('user.index')" :active="request()->routeIs('user.index')">
                        <i class="fa-solid fa-book size-[1.2em]"></i>
                        <span class="ml-2">{{ __('Library') }}</span>
                    </x-nav-link>
                </div>
            </div>

            <!-- Barra de búsqueda (responsive) -->
            <div class="flex-1 flex sm:hidden items-center justify-center px-2 lg:ml-6"
                 :class="{ 'absolute inset-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm z-50': searchOpen }">
                @if(!request()->routeIs('manga.search'))
                    <form method="POST" action="{{ route('manga.search.perform') }}"
                          class="flex flex-col items-center gap-4">
                        @csrf
                        <x-search-nav-input></x-search-nav-input>
                    </form>
                @endif
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Barra de búsqueda -->
                @if(!request()->routeIs('manga.search'))
                    <form method="POST" action="{{ route('manga.search.perform') }}"
                          class="flex flex-col items-center gap-4">
                        @csrf
                        <x-search-nav-input></x-search-nav-input>
                    </form>
                @endif
                <div class="ml-6">
                    <x-user-profile :user="Auth::user()" class="w-12 h-12 rounded-full"/>
                </div>
                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Dark Mode Toggle -->
                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="fa-solid fa-user size-[1.2em]"></i>
                            <span class="ml-2">{{ __('Profile') }}</span>
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link class="border-b border-gray-200 dark:border-gray-600"
                                                :href="route('logout')"
                                                onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <i class="fa-solid fa-right-to-bracket size-[1.2em]"></i>
                                <span class="ml-2">{{ __('Log Out') }}</span>
                            </x-dropdown-link>
                        </form>
                        <x-dark-button class="hidden sm:flex"/>
                        <x-language-button class="hidden sm:flex"/>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <label class="btn btn-circle swap swap-rotate text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <!-- Este checkbox controla el estado del swap -->
                    <input type="checkbox" x-model="open" class="hidden" />

                    <!-- Icono hamburguesa -->
                    <svg
                        class="swap-off fill-current"
                        xmlns="http://www.w3.org/2000/svg"
                        width="32"
                        height="32"
                        viewBox="0 0 512 512">
                        <path d="M64,384H448V341.33H64Zm0-106.67H448V234.67H64ZM64,128v42.67H448V128Z" />
                    </svg>

                    <!-- Icono cerrar -->
                    <svg
                        class="swap-on fill-current"
                        xmlns="http://www.w3.org/2000/svg"
                        width="32"
                        height="32"
                        viewBox="0 0 512 512">
                        <polygon
                            points="400 145.49 366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49" />
                    </svg>
                </label>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-b border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <x-user-profile :user="Auth::user()" class="w-16 h-16 rounded-full"/>
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <i class="fa-solid fa-user"></i>
                    <span class="ml-2">{{ __('Profile') }}</span>
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                                           onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span class="ml-2">{{ __('Log Out') }}</span>
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>

        <div class="pt-2 pb-3 space-y-1">
            <x-dark-button class="flex"/>
            <x-language-button class="flex"/>
        </div>
    </div>
</nav>
