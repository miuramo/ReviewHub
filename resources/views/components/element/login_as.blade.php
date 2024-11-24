@props([
    'user' => null,
])
<!-- components.element.loginas -->
@isset($user)
<a class="hover:bg-pink-100 underline font-bold" href="{{ route('role.login-as', ['user'=>$user->id ])}}">{{ $user->name ?? '---' }}</a>
@else
---
@endif