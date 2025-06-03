<div class="fixed z-50 transition-all duration-500 ease-out"
     x-data="{
         showButton: false,
         isActive: false
     }"
     x-init="
         const header = document.querySelector('header') || document.getElementById('header');
         const headerHeight = header ? header.offsetHeight : 100;

         window.addEventListener('scroll', () => {
             const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
             showButton = currentScroll > headerHeight;
         });
     "
     :class="{
         'top-6 left-1/2 transform -translate-x-1/2 opacity-100': showButton,
         '-top-20 left-1/2 transform -translate-x-1/2 opacity-0': !showButton
     }">
    <x-link-button
        class="!rounded-full flex text-center !px-0 items-center justify-center w-10 h-10 bg-white dark:bg-gray-800 shadow-lg group"
        @click.prevent="
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        "
        @mousedown="isActive = true"
        @mouseup="isActive = false"
        @mouseleave="isActive = false"
        @focus="isActive = true"
        @blur="isActive = false">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
             class="h-3 w-3 transition-all duration-500 flex items-center justify-center ease-out text-gray-500 dark:text-gray-400 group-hover:text-indigo-500"
             :class="{
                 'scale-110': showButton,
                 'text-indigo-500': isActive
             }"
             fill="currentColor">
            <path d="M246.6 41.4c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L224 109.3 361.4 246.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160zm160 352l-160-160c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L224 301.3 361.4 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3z"/>
        </svg>
    </x-link-button>
</div>
