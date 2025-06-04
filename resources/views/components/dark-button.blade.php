<div x-data="{
        dark: localStorage.getItem('theme') === 'dark',
        toggle() {
            this.dark = !this.dark;
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', this.dark);
        }
    }"
     x-init="document.documentElement.classList.toggle('dark', dark)"
    {{ $attributes->merge(['class' => 'flex items-center']) }}>
    <button @click="toggle"
            class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out"
    >
        <i :class="dark ? 'fas fa-sun ' : 'fas fa-moon'"></i>
        <span class="ms-2"
              x-text="dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'"></span>
    </button>
</div>
