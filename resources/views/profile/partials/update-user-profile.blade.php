<section>
    <header class="flex flex-col items-start gap-2">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Picture') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile picture.") }}
        </p>
        <x-user-profile :user="Auth::user()" class="w-40 h-40"  />
    </header>

    <form action="{{ route('profile.update.photo') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="flex flex-col items-start gap-6 mt-3">
            <x-file-input name="photo"></x-file-input>
            <x-input-error :messages="$errors->get('photo')" />
            <x-primary-button>{{ __('Save Photo') }}</x-primary-button>
        </div>
    </form>

</section>
