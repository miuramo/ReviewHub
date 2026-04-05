<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Specialities') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Add (or update) your specialities.") }}
        </p>
    </header>

    <livewire:speciality-editor :user_id="$user->id" />

</section>
