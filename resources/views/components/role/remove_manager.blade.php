@props([
    'submit_id' => null,
    'user_id' => null,
])
@php
    $sub = App\Models\Submit::find($submit_id);
@endphp
{{-- <x-element.component_name>
    remove_manager
</x-element.component_name> --}}

<!-- components.role.add_rev -->
<form action="{{ route('role.remove_manager') }}" method="post" class="inline-flex">
    @csrf
    @method('PUT')
    <input type="hidden" name="paper_id" value="{{ $sub->paper->id }}">
    <input type="hidden" name="user_id" value="{{ $user_id }}">
    <input type="hidden" name="redirect_page" value="{{ route('role.top', ['role' => 'ec']) }}">
    <x-element.submitbutton2 color="orange" size="sm" confirm="本当に脱退する？">
        脱退する
    </x-element.submitbutton2>
</form>
