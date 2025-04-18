@props(['user'])

<div class="flex items-center justify-center rounded-lg">
    <img {{ $attributes->merge(['class' => 'object-cover']) }}
         src="{{ $profilePhoto }}"
         alt="{{ $user->name }} profile picture">
</div>
