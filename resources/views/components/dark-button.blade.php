<div x-data="{
        dark: localStorage.getItem('theme') === 'dark',
        toggle() {
            this.dark = !this.dark;
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', this.dark);
        }
    }"
     x-init="document.documentElement.classList.toggle('dark', dark)"
     {{ $attributes->merge(['class' => 'absolute top-0 right-0 flex items-center']) }}>
    <button @click="toggle"
            class="flex items-center gap-2 px-3 py-2 rounded-bl text-sm font-medium text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
    >
        <i :class="dark ? 'fas fa-sun ' : 'fas fa-moon'"></i>
    </button>
</div>
