<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
<div class="sm:pt-10 min-h-screen bg-gray-100 dark:bg-gray-900">
    @include('layouts.navigation')

    <!-- Page Heading -->
    @isset($header)
        <header>
            <div class="!text-4xl max-w-7xl mx-auto pt-6 sm:pt-0 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <!-- Page Content -->
    <main class="pb-16 sm:pb-0">
        <x-back-to-top></x-back-to-top>
        {{ $slot }}
    </main>
</div>
<div class="md:hidden z-[999] dock dock-md border-t border-1 border-gray-600 shadow-lg rounded-t-xl bg-white dark:bg-gray-800 text-gray-800 dark:text-white">
    <x-nav-link class="text-center"
                :href="route('manga.search')"
                :active="request()->routeIs('manga.search')"
                :class="request()->routeIs('manga.search') ? 'border-indigo-500 dark:border-indigo-600 bg-indigo-100 dark:bg-indigo-900/50' : ''"> {{-- ? 'dock-active' : ''"--}}
        <i class="fa-solid fa-home size-[1.2em]"></i>
        <span class="dock-label">{{__('Home')}}</span>
    </x-nav-link>
    <x-nav-link class="text-center"
                :href="route('user.index')"
                :active="request()->routeIs('user.index')"
                :class="request()->routeIs('user.index') ? 'border-indigo-500 dark:border-indigo-600 bg-indigo-100 dark:bg-indigo-900/50' : ''"> {{-- ? 'dock-active' : ''"--}}
        <i class="fa-solid fa-book size-[1.2em]"></i>
        <span class="dock-label">{{__('Library')}}</span>
    </x-nav-link>
</div>
</body>
</html>
