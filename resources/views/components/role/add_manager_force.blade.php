@props([
    'submit_id' => null,
    'user_id' => null,
])
@php
    $sub = App\Models\Submit::find($submit_id);
@endphp

<!-- components.role.add_rev -->
<form action="{{ route('role.add_manager_force') }}" method="post" class="inline-flex">
    @csrf
    @method('PUT')
    <input type="hidden" name="paper_id" value="{{ $sub->paper->id }}">
    <input type="hidden" name="user_id" value="{{ $user_id }}">
    <x-element.submitbutton2 color="green" size="sm">
        追加
    </x-element.submitbutton2>
</form>
