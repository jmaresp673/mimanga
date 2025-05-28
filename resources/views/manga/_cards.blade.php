@forelse($results as $manga)
    @include('manga._card', ['manga' => $manga])
@empty
    <x-no-more-results>{{ __('No further results found.') }}</x-no-more-results>
@endforelse
